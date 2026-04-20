<?php

declare(strict_types = 1);

namespace Centrex\Crm\Commands;

use Centrex\Crm\Crm;
use Centrex\Crm\Enums\LeadStatus;
use Centrex\Crm\Models\Lead;
use Illuminate\Console\Command;

class ScoreLeadsCommand extends Command
{
    public $signature = 'crm:score-leads
        {--status=open : Lead status to score (open|qualified|all)}';

    public $description = 'Re-score CRM leads based on value, activity, and probability';

    public function handle(Crm $crm): int
    {
        $statusFilter = $this->option('status');

        $query = Lead::query();

        if ($statusFilter !== 'all') {
            $status = $statusFilter === 'qualified' ? LeadStatus::Qualified : LeadStatus::Open;
            $query->where('status', $status->value);
        }

        $count = 0;
        $query->each(function (Lead $lead) use ($crm, &$count): void {
            $crm->scoreLead($lead);
            $count++;
        });

        $this->info("Scored {$count} leads.");

        return self::SUCCESS;
    }
}
