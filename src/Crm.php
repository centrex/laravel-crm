<?php

declare(strict_types = 1);

namespace Centrex\Crm;

use Centrex\Crm\Enums\{DealStage, LeadSource, LeadStatus};
use Centrex\Crm\Exceptions\{InvalidDealStageTransition, InvalidLeadStatusTransition};
use Centrex\Crm\Models\{Activity, ClvSnapshot, Contact, Deal, Lead};
use Centrex\Crm\Services\ClvCalculator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Crm
{
    public function __construct(private readonly ClvCalculator $clvCalculator = new ClvCalculator()) {}

    /* -------------------------
     * Lead lifecycle
     * ------------------------- */

    public function createLead(array $attributes): Lead
    {
        $attributes['code'] ??= $this->nextCode('LEAD');
        $attributes['status'] ??= LeadStatus::Open->value;
        $attributes['currency'] ??= 'BDT';

        return Lead::query()->create($attributes);
    }

    public function qualifyLead(Lead $lead, array $dealAttributes = []): Deal
    {
        if ($lead->status !== LeadStatus::Open) {
            throw new InvalidLeadStatusTransition('Only open leads can be qualified.');
        }

        return DB::connection($lead->getConnectionName())->transaction(function () use ($lead, $dealAttributes): Deal {
            $deal = Deal::query()->create([
                'code'                => $dealAttributes['code'] ?? $this->nextCode('DEAL'),
                'lead_id'             => $lead->id,
                'company_id'          => $dealAttributes['company_id'] ?? $lead->company_id,
                'contact_id'          => $dealAttributes['contact_id'] ?? $lead->contact_id,
                'name'                => $dealAttributes['name'] ?? $lead->title,
                'stage'               => $dealAttributes['stage'] ?? DealStage::Qualified->value,
                'amount'              => $dealAttributes['amount'] ?? $lead->value,
                'currency'            => $dealAttributes['currency'] ?? $lead->currency,
                'probability'         => $dealAttributes['probability'] ?? max($lead->probability, 20),
                'expected_close_date' => $dealAttributes['expected_close_date'] ?? null,
                'owner_id'            => $dealAttributes['owner_id'] ?? $lead->owner_id,
                'notes'               => $dealAttributes['notes'] ?? $lead->notes,
                'meta'                => $dealAttributes['meta'] ?? $lead->meta,
            ]);

            $lead->forceFill([
                'status'       => LeadStatus::Qualified->value,
                'qualified_at' => now(),
            ])->save();

            return $deal;
        });
    }

    public function markLeadLost(Lead $lead, ?string $reason = null): Lead
    {
        if ($lead->status !== LeadStatus::Open) {
            throw new InvalidLeadStatusTransition('Only open leads can be marked as lost.');
        }

        $meta = $lead->meta ?? [];

        if ($reason !== null) {
            $meta['loss_reason'] = $reason;
        }

        $lead->forceFill([
            'status'  => LeadStatus::Lost->value,
            'lost_at' => now(),
            'meta'    => $meta,
        ])->save();

        return $lead->refresh();
    }

    /* -------------------------
     * Deal pipeline
     * ------------------------- */

    public function advanceDealStage(Deal $deal, DealStage|string $targetStage): Deal
    {
        $targetStage = $targetStage instanceof DealStage ? $targetStage : DealStage::from($targetStage);
        $currentStage = $deal->stage;

        if ($currentStage->isClosed()) {
            throw new InvalidDealStageTransition('Closed deals cannot be reopened through the default pipeline flow.');
        }

        $allowedTransitions = [
            DealStage::Qualified->value   => [DealStage::Proposal, DealStage::Lost],
            DealStage::Proposal->value    => [DealStage::Negotiation, DealStage::Lost],
            DealStage::Negotiation->value => [DealStage::Won, DealStage::Lost],
        ];

        $nextStages = array_map(
            static fn (DealStage $stage): string => $stage->value,
            $allowedTransitions[$currentStage->value] ?? [],
        );

        if (!in_array($targetStage->value, $nextStages, true)) {
            throw new InvalidDealStageTransition(sprintf(
                'Cannot move deal from [%s] to [%s].',
                $currentStage->value,
                $targetStage->value,
            ));
        }

        $deal->stage = $targetStage;
        $deal->probability = match ($targetStage) {
            DealStage::Proposal    => max($deal->probability, 40),
            DealStage::Negotiation => max($deal->probability, 70),
            DealStage::Won         => 100,
            DealStage::Lost        => 0,
            default                => $deal->probability,
        };
        $deal->won_at = $targetStage === DealStage::Won ? now() : $deal->won_at;
        $deal->lost_at = $targetStage === DealStage::Lost ? now() : $deal->lost_at;
        $deal->save();

        return $deal->refresh();
    }

    /* -------------------------
     * Activity logging
     * ------------------------- */

    public function logActivity(Model $subject, array $attributes): Activity
    {
        return $subject->morphMany(Activity::class, 'subject')->create([
            'type'         => $attributes['type'] ?? 'note',
            'priority'     => $attributes['priority'] ?? 'normal',
            'summary'      => $attributes['summary'] ?? 'Follow up',
            'description'  => $attributes['description'] ?? null,
            'due_at'       => $attributes['due_at'] ?? null,
            'completed_at' => $attributes['completed_at'] ?? null,
            'owner_id'     => $attributes['owner_id'] ?? null,
            'meta'         => $attributes['meta'] ?? null,
        ]);
    }

    /* -------------------------
     * CLV Calculation
     * ------------------------- */

    public function calculateClv(Contact $contact, int $horizonMonths = 12): ClvSnapshot
    {
        $transactions = $contact->wonDealsAsTransactions();
        $result = $this->clvCalculator->calculate($transactions, $horizonMonths);

        return ClvSnapshot::query()->updateOrCreate(
            ['contact_id' => $contact->id, 'horizon_months' => $horizonMonths],
            array_merge($result, ['calculated_at' => now()]),
        );
    }

    public function recalculateAllClv(int $horizonMonths = 12): int
    {
        $count = 0;

        Contact::query()->each(function (Contact $contact) use ($horizonMonths, &$count): void {
            $this->calculateClv($contact, $horizonMonths);
            $count++;
        });

        return $count;
    }

    public function getClvLeaderboard(int $limit = 10, int $horizonMonths = 12): Collection
    {
        return ClvSnapshot::query()
            ->where('horizon_months', $horizonMonths)
            ->with('contact')
            ->orderByDesc('clv_value')
            ->limit($limit)
            ->get();
    }

    /* -------------------------
     * Lead scoring
     * ------------------------- */

    public function scoreLead(Lead $lead): Lead
    {
        $score = 0;

        $value = (float) $lead->value;
        $score += match (true) {
            $value >= 500000 => 30,
            $value >= 100000 => 20,
            $value >= 50000  => 15,
            $value >= 10000  => 10,
            default          => 5,
        };

        $score += min(20, (int) $lead->probability / 5);

        $sourceBonus = match ($lead->source) {
            LeadSource::Referral  => 20,
            LeadSource::Partner   => 15,
            LeadSource::Event     => 10,
            LeadSource::Web       => 8,
            LeadSource::SocialMedia => 5,
            default               => 0,
        };
        $score += $sourceBonus;

        $activityCount = $lead->activities()->count();
        $score += min(15, $activityCount * 3);

        $score += match (true) {
            $lead->company_id !== null && $lead->contact_id !== null => 10,
            $lead->company_id !== null || $lead->contact_id !== null => 5,
            default                                                   => 0,
        };

        $lead->forceFill(['score' => min(100, $score)])->save();

        return $lead->refresh();
    }

    /* -------------------------
     * Reporting & analytics
     * ------------------------- */

    public function getPipelineSummary(): array
    {
        $activeDealStages = [
            DealStage::Qualified->value,
            DealStage::Proposal->value,
            DealStage::Negotiation->value,
        ];

        $weightedPipelineValue = Deal::query()
            ->whereIn('stage', $activeDealStages)
            ->get()
            ->sum(static fn (Deal $deal): float => ((float) $deal->amount * $deal->probability) / 100);

        return [
            'open_leads'              => Lead::query()->where('status', LeadStatus::Open->value)->count(),
            'qualified_leads'         => Lead::query()->where('status', LeadStatus::Qualified->value)->count(),
            'lost_leads'              => Lead::query()->where('status', LeadStatus::Lost->value)->count(),
            'active_deals'            => Deal::query()->whereIn('stage', $activeDealStages)->count(),
            'won_deals'               => Deal::query()->where('stage', DealStage::Won->value)->count(),
            'lost_deals'              => Deal::query()->where('stage', DealStage::Lost->value)->count(),
            'pipeline_value'          => (float) Deal::query()->whereIn('stage', $activeDealStages)->sum('amount'),
            'weighted_pipeline_value' => round($weightedPipelineValue, 2),
        ];
    }

    public function dealsByStage(): array
    {
        return Deal::query()
            ->selectRaw('stage, COUNT(*) as count, COALESCE(SUM(amount), 0) as amount')
            ->groupBy('stage')
            ->orderByRaw("CASE stage
                WHEN 'qualified' THEN 1
                WHEN 'proposal' THEN 2
                WHEN 'negotiation' THEN 3
                WHEN 'won' THEN 4
                WHEN 'lost' THEN 5
                ELSE 99 END")
            ->get()
            ->map(static fn (Deal $deal): array => [
                'stage'  => $deal->stage->value,
                'count'  => (int) $deal->getAttribute('count'),
                'amount' => (float) $deal->getAttribute('amount'),
            ])
            ->all();
    }

    public function getConversionRates(): array
    {
        $totalLeads = Lead::query()->count();
        $qualifiedLeads = Lead::query()->where('status', LeadStatus::Qualified->value)->count();
        $lostLeads = Lead::query()->where('status', LeadStatus::Lost->value)->count();
        $wonDeals = Deal::query()->where('stage', DealStage::Won->value)->count();
        $totalDeals = Deal::query()->count();

        return [
            'lead_to_qualified'  => $totalLeads > 0 ? round($qualifiedLeads / $totalLeads * 100, 1) : 0.0,
            'lead_to_lost'       => $totalLeads > 0 ? round($lostLeads / $totalLeads * 100, 1) : 0.0,
            'deal_win_rate'      => $totalDeals > 0 ? round($wonDeals / $totalDeals * 100, 1) : 0.0,
            'total_leads'        => $totalLeads,
            'qualified_leads'    => $qualifiedLeads,
            'lost_leads'         => $lostLeads,
            'won_deals'          => $wonDeals,
            'total_deals'        => $totalDeals,
        ];
    }

    public function getRevenueForecast(int $months = 3): array
    {
        $activeDealStages = [
            DealStage::Qualified->value,
            DealStage::Proposal->value,
            DealStage::Negotiation->value,
        ];

        $deals = Deal::query()
            ->whereIn('stage', $activeDealStages)
            ->whereNotNull('expected_close_date')
            ->where('expected_close_date', '<=', now()->addMonths($months))
            ->get();

        $forecast = [];

        for ($i = 1; $i <= $months; $i++) {
            $monthStart = now()->startOfMonth()->addMonths($i - 1);
            $monthEnd = now()->startOfMonth()->addMonths($i)->subDay();

            $monthDeals = $deals->filter(static fn (Deal $deal): bool => $deal->expected_close_date->between($monthStart, $monthEnd));

            $forecast[] = [
                'month'              => $monthStart->format('Y-m'),
                'deal_count'         => $monthDeals->count(),
                'expected_revenue'   => round($monthDeals->sum('amount'), 2),
                'weighted_revenue'   => round($monthDeals->sum(static fn (Deal $d): float => (float) $d->amount * $d->probability / 100), 2),
            ];
        }

        return $forecast;
    }

    public function getTopOwners(int $limit = 5): Collection
    {
        return Deal::query()
            ->selectRaw('owner_id, COUNT(*) as deal_count, SUM(amount) as total_amount, SUM(CASE WHEN stage = ? THEN 1 ELSE 0 END) as won_count', [DealStage::Won->value])
            ->whereNotNull('owner_id')
            ->groupBy('owner_id')
            ->orderByDesc('won_count')
            ->limit($limit)
            ->get();
    }

    /* -------------------------
     * Search
     * ------------------------- */

    public function searchContacts(string $query, int $limit = 20): Collection
    {
        return Contact::query()
            ->with('company')
            ->where(static function ($q) use ($query): void {
                $q->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get();
    }

    public function searchCompanies(string $query, int $limit = 20): Collection
    {
        return \Centrex\Crm\Models\Company::query()
            ->where(static function ($q) use ($query): void {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get();
    }

    /* -------------------------
     * Activity helpers
     * ------------------------- */

    public function upcomingActivities(int $limit = 5): Collection
    {
        return Activity::query()
            ->whereNull('completed_at')
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->limit($limit)
            ->get();
    }

    public function getOverdueActivities(): Collection
    {
        return Activity::query()
            ->whereNull('completed_at')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->orderBy('due_at')
            ->get();
    }

    /* -------------------------
     * Private helpers
     * ------------------------- */

    private function nextCode(string $prefix): string
    {
        return sprintf('%s-%s', $prefix, strtoupper((string) str()->random(8)));
    }
}
