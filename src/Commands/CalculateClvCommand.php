<?php

declare(strict_types = 1);

namespace Centrex\Crm\Commands;

use Centrex\Crm\Crm;
use Centrex\Crm\Models\Contact;
use Illuminate\Console\Command;

class CalculateClvCommand extends Command
{
    public $signature = 'crm:calculate-clv
        {--contact= : Only recalculate for a specific contact ID}
        {--horizon=12 : Forecast horizon in months}';

    public $description = 'Calculate Customer Lifetime Value (CLV) for CRM contacts';

    public function handle(Crm $crm): int
    {
        $horizonMonths = (int) $this->option('horizon');
        $contactId = $this->option('contact');

        if ($contactId !== null) {
            $contact = Contact::query()->find((int) $contactId);

            if ($contact === null) {
                $this->error("Contact #{$contactId} not found.");

                return self::FAILURE;
            }

            $snapshot = $crm->calculateClv($contact, $horizonMonths);
            $this->info("CLV for {$contact->full_name}: {$snapshot->clv_value}");

            return self::SUCCESS;
        }

        $count = $crm->recalculateAllClv($horizonMonths);
        $this->info("CLV calculated for {$count} contacts (horizon: {$horizonMonths} months).");

        return self::SUCCESS;
    }
}
