<?php

declare(strict_types = 1);

namespace Centrex\Crm;

use Centrex\Crm\Enums\{DealStage, LeadStatus};
use Centrex\Crm\Exceptions\{InvalidDealStageTransition, InvalidLeadStatusTransition};
use Centrex\Crm\Models\{Activity, Deal, Lead};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Crm
{
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
                'code' => $dealAttributes['code'] ?? $this->nextCode('DEAL'),
                'lead_id' => $lead->id,
                'company_id' => $dealAttributes['company_id'] ?? $lead->company_id,
                'contact_id' => $dealAttributes['contact_id'] ?? $lead->contact_id,
                'name' => $dealAttributes['name'] ?? $lead->title,
                'stage' => $dealAttributes['stage'] ?? DealStage::Qualified->value,
                'amount' => $dealAttributes['amount'] ?? $lead->value,
                'currency' => $dealAttributes['currency'] ?? $lead->currency,
                'probability' => $dealAttributes['probability'] ?? max($lead->probability, 20),
                'expected_close_date' => $dealAttributes['expected_close_date'] ?? null,
                'owner_id' => $dealAttributes['owner_id'] ?? $lead->owner_id,
                'notes' => $dealAttributes['notes'] ?? $lead->notes,
                'meta' => $dealAttributes['meta'] ?? $lead->meta,
            ]);

            $lead->forceFill([
                'status' => LeadStatus::Qualified->value,
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
            'status' => LeadStatus::Lost->value,
            'lost_at' => now(),
            'meta' => $meta,
        ])->save();

        return $lead->refresh();
    }

    public function advanceDealStage(Deal $deal, DealStage|string $targetStage): Deal
    {
        $targetStage = $targetStage instanceof DealStage ? $targetStage : DealStage::from($targetStage);
        $currentStage = $deal->stage;

        if ($currentStage->isClosed()) {
            throw new InvalidDealStageTransition('Closed deals cannot be reopened through the default pipeline flow.');
        }

        $allowedTransitions = [
            DealStage::Qualified->value => [DealStage::Proposal, DealStage::Lost],
            DealStage::Proposal->value => [DealStage::Negotiation, DealStage::Lost],
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
            DealStage::Proposal => max($deal->probability, 40),
            DealStage::Negotiation => max($deal->probability, 70),
            DealStage::Won => 100,
            DealStage::Lost => 0,
            default => $deal->probability,
        };
        $deal->won_at = $targetStage === DealStage::Won ? now() : $deal->won_at;
        $deal->lost_at = $targetStage === DealStage::Lost ? now() : $deal->lost_at;
        $deal->save();

        return $deal->refresh();
    }

    public function logActivity(Model $subject, array $attributes): Activity
    {
        return $subject->morphMany(Activity::class, 'subject')->create([
            'type' => $attributes['type'] ?? 'note',
            'summary' => $attributes['summary'] ?? 'Follow up',
            'description' => $attributes['description'] ?? null,
            'due_at' => $attributes['due_at'] ?? null,
            'completed_at' => $attributes['completed_at'] ?? null,
            'owner_id' => $attributes['owner_id'] ?? null,
            'meta' => $attributes['meta'] ?? null,
        ]);
    }

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
            'open_leads' => Lead::query()->where('status', LeadStatus::Open->value)->count(),
            'qualified_leads' => Lead::query()->where('status', LeadStatus::Qualified->value)->count(),
            'lost_leads' => Lead::query()->where('status', LeadStatus::Lost->value)->count(),
            'active_deals' => Deal::query()->whereIn('stage', $activeDealStages)->count(),
            'won_deals' => Deal::query()->where('stage', DealStage::Won->value)->count(),
            'lost_deals' => Deal::query()->where('stage', DealStage::Lost->value)->count(),
            'pipeline_value' => (float) Deal::query()->whereIn('stage', $activeDealStages)->sum('amount'),
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
                'stage' => $deal->stage->value,
                'count' => (int) $deal->getAttribute('count'),
                'amount' => (float) $deal->getAttribute('amount'),
            ])
            ->all();
    }

    public function upcomingActivities(int $limit = 5): Collection
    {
        return Activity::query()
            ->whereNull('completed_at')
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->limit($limit)
            ->get();
    }

    private function nextCode(string $prefix): string
    {
        return sprintf('%s-%s', $prefix, strtoupper((string) str()->random(8)));
    }
}
