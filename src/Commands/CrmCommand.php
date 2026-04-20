<?php

declare(strict_types = 1);

namespace Centrex\Crm\Commands;

use Illuminate\Console\Command;

class CrmCommand extends Command
{
    public $signature = 'crm:summary';

    public $description = 'Display a quick CRM pipeline summary';

    public function handle(): int
    {
        /** @var \Centrex\Crm\Crm $crm */
        $crm = app(\Centrex\Crm\Crm::class);
        $summary = $crm->getPipelineSummary();

        $this->table(['Metric', 'Value'], collect($summary)->map(
            static fn (mixed $value, string $key): array => [$key, (string) $value],
        ));

        return self::SUCCESS;
    }
}
