<?php

declare(strict_types = 1);

use Centrex\Crm\Crm;
use Centrex\Crm\Enums\{ActivityType, DealStage, LeadStatus};
use Centrex\Crm\Exceptions\{InvalidDealStageTransition, InvalidLeadStatusTransition};
use Centrex\Crm\Models\Deal;

it('creates and qualifies a lead into a deal', function () {
    $crm = app(Crm::class);

    $lead = $crm->createLead([
        'title'       => 'ERP rollout for Alpine Foods',
        'source'      => 'website',
        'value'       => 150000,
        'probability' => 25,
    ]);

    $deal = $crm->qualifyLead($lead, [
        'expected_close_date' => '2026-05-30',
    ]);

    expect($lead->fresh()->status)->toBe(LeadStatus::Qualified)
        ->and($deal->stage)->toBe(DealStage::Qualified)
        ->and((float) $deal->amount)->toBe(150000.0);
});

it('can move a deal through the default pipeline', function () {
    $crm = app(Crm::class);
    $lead = $crm->createLead([
        'title' => 'Warehouse digitization',
        'value' => 90000,
    ]);
    $deal = $crm->qualifyLead($lead);

    $crm->advanceDealStage($deal, DealStage::Proposal);
    $crm->advanceDealStage($deal->fresh(), DealStage::Negotiation);
    $wonDeal = $crm->advanceDealStage($deal->fresh(), DealStage::Won);

    expect($wonDeal->stage)->toBe(DealStage::Won)
        ->and($wonDeal->probability)->toBe(100)
        ->and($wonDeal->won_at)->not->toBeNull();
});

it('rejects invalid lead and deal transitions', function () {
    $crm = app(Crm::class);
    $lead = $crm->createLead([
        'title' => 'Support retainer',
        'value' => 12000,
    ]);

    $crm->markLeadLost($lead, 'Budget frozen');

    expect(fn () => $crm->qualifyLead($lead->fresh()))
        ->toThrow(InvalidLeadStatusTransition::class);

    $openLead = $crm->createLead([
        'title' => 'POS rollout',
        'value' => 50000,
    ]);
    $deal = $crm->qualifyLead($openLead);

    expect(fn () => $crm->advanceDealStage($deal, DealStage::Won))
        ->toThrow(InvalidDealStageTransition::class);
});

it('summarises pipeline value and logs activities', function () {
    $crm = app(Crm::class);

    $leadA = $crm->createLead([
        'title'       => 'Deal A',
        'value'       => 100000,
        'probability' => 30,
    ]);
    $dealA = $crm->qualifyLead($leadA, ['probability' => 40]);

    $leadB = $crm->createLead([
        'title'       => 'Deal B',
        'value'       => 50000,
        'probability' => 20,
    ]);
    $dealB = $crm->qualifyLead($leadB, ['probability' => 70]);
    $crm->advanceDealStage($dealB, DealStage::Proposal);

    $activity = $crm->logActivity($dealA, [
        'type'    => ActivityType::Call->value,
        'summary' => 'Discovery call',
        'due_at'  => now()->addDay(),
    ]);

    $summary = $crm->getPipelineSummary();
    $stages = collect($crm->dealsByStage())->keyBy('stage');

    expect($activity->subject)->toBeInstanceOf(Deal::class)
        ->and($summary['active_deals'])->toBe(2)
        ->and($summary['pipeline_value'])->toBe(150000.0)
        ->and($summary['weighted_pipeline_value'])->toBe(75000.0)
        ->and($stages['qualified']['count'])->toBe(1)
        ->and($stages['proposal']['count'])->toBe(1);
});
