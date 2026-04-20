<?php

declare(strict_types = 1);

use Centrex\Crm\Crm;
use Centrex\Crm\Enums\{ActivityType, DealStage, LeadSource, LeadStatus};
use Centrex\Crm\Exceptions\{InvalidDealStageTransition, InvalidLeadStatusTransition};
use Centrex\Crm\Models\{Activity, ClvSnapshot, Company, Contact, Deal, Lead, Tag};

it('creates and qualifies a lead into a deal', function () {
    $crm = app(Crm::class);

    $lead = $crm->createLead([
        'title'       => 'ERP rollout for Alpine Foods',
        'source'      => LeadSource::Web->value,
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

it('supports tags on companies, contacts, leads, and deals', function () {
    $company = Company::factory()->create();
    $contact = Contact::factory()->create();
    $crm = app(Crm::class);
    $lead = $crm->createLead(['title' => 'Tagged lead', 'value' => 10000]);

    $company->attachTag('enterprise');
    $contact->attachTag('decision-maker');
    $lead->syncTags(['hot', 'enterprise']);

    expect($company->tags)->toHaveCount(1)
        ->and($company->tags->first()->slug)->toBe('enterprise')
        ->and($contact->tags)->toHaveCount(1)
        ->and($lead->tags)->toHaveCount(2);
});

it('calculates CLV for a contact with won deals', function () {
    $crm = app(Crm::class);
    $contact = Contact::factory()->create();

    $lead1 = $crm->createLead(['title' => 'Deal 1', 'value' => 50000, 'contact_id' => $contact->id]);
    $deal1 = $crm->qualifyLead($lead1);
    $crm->advanceDealStage($deal1, DealStage::Proposal);
    $crm->advanceDealStage($deal1->fresh(), DealStage::Negotiation);
    $crm->advanceDealStage($deal1->fresh(), DealStage::Won);

    $deal1->fresh()->forceFill(['won_at' => now()->subMonths(6)])->save();

    $snapshot = $crm->calculateClv($contact->fresh(), 12);

    expect($snapshot)->toBeInstanceOf(ClvSnapshot::class)
        ->and($snapshot->contact_id)->toBe($contact->id)
        ->and($snapshot->horizon_months)->toBe(12)
        ->and($snapshot->frequency)->toBeGreaterThanOrEqual(0);
});

it('returns zero CLV for contacts with no won deals', function () {
    $crm = app(Crm::class);
    $contact = Contact::factory()->create();

    $snapshot = $crm->calculateClv($contact, 12);

    expect((float) $snapshot->clv_value)->toBe(0.0)
        ->and($snapshot->frequency)->toBe(0);
});

it('scores a lead based on value, source, and activity', function () {
    $crm = app(Crm::class);

    $lead = $crm->createLead([
        'title'       => 'High-value referral',
        'value'       => 200000,
        'source'      => LeadSource::Referral->value,
        'probability' => 40,
    ]);

    $company = Company::factory()->create();
    $lead->forceFill(['company_id' => $company->id])->save();

    $crm->logActivity($lead, ['type' => ActivityType::Call->value, 'summary' => 'Intro call']);
    $crm->logActivity($lead, ['type' => ActivityType::Meeting->value, 'summary' => 'Demo']);

    $scored = $crm->scoreLead($lead->fresh());

    expect($scored->score)->toBeGreaterThan(50);
});

it('returns conversion rates', function () {
    $crm = app(Crm::class);

    $openLead = $crm->createLead(['title' => 'Open', 'value' => 10000]);
    $lostLead = $crm->createLead(['title' => 'Lost', 'value' => 5000]);
    $crm->markLeadLost($lostLead, 'No budget');

    $qualifiedLead = $crm->createLead(['title' => 'Won', 'value' => 80000]);
    $deal = $crm->qualifyLead($qualifiedLead);
    $crm->advanceDealStage($deal, DealStage::Proposal);
    $crm->advanceDealStage($deal->fresh(), DealStage::Negotiation);
    $crm->advanceDealStage($deal->fresh(), DealStage::Won);

    $rates = $crm->getConversionRates();

    expect($rates['total_leads'])->toBe(3)
        ->and($rates['won_deals'])->toBe(1)
        ->and($rates['deal_win_rate'])->toBeGreaterThan(0.0);
});

it('retrieves overdue and upcoming activities', function () {
    $crm = app(Crm::class);

    $lead = $crm->createLead(['title' => 'Activity test', 'value' => 20000]);

    $crm->logActivity($lead, [
        'type'    => ActivityType::Task->value,
        'summary' => 'Follow up',
        'due_at'  => now()->subDay(),
    ]);
    $crm->logActivity($lead, [
        'type'    => ActivityType::Call->value,
        'summary' => 'Discovery',
        'due_at'  => now()->addDay(),
    ]);

    $overdue = $crm->getOverdueActivities();
    $upcoming = $crm->upcomingActivities();

    expect($overdue)->toHaveCount(1)
        ->and($upcoming)->toHaveCount(2);
});

it('can attach activities to companies and contacts via HasActivities', function () {
    $crm = app(Crm::class);
    $company = Company::factory()->create();
    $contact = Contact::factory()->create();

    $crm->logActivity($company, ['type' => ActivityType::Note->value, 'summary' => 'Company note']);
    $crm->logActivity($contact, ['type' => ActivityType::Email->value, 'summary' => 'Sent intro email']);

    expect($company->activities)->toHaveCount(1)
        ->and($contact->activities)->toHaveCount(1)
        ->and($company->activities->first()->subject)->toBeInstanceOf(Company::class);
});

it('searches contacts and companies', function () {
    $crm = app(Crm::class);

    Contact::factory()->create(['first_name' => 'Alice', 'last_name' => 'Walker', 'email' => 'alice@example.com']);
    Contact::factory()->create(['first_name' => 'Bob', 'last_name' => 'Smith']);
    Company::factory()->create(['name' => 'Acme Corp']);
    Company::factory()->create(['name' => 'Beta Ltd']);

    $contacts = $crm->searchContacts('alice');
    $companies = $crm->searchCompanies('acme');

    expect($contacts)->toHaveCount(1)
        ->and($contacts->first()->first_name)->toBe('Alice')
        ->and($companies)->toHaveCount(1)
        ->and($companies->first()->name)->toBe('Acme Corp');
});
