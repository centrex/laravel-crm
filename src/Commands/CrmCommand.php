<?php

declare(strict_types = 1);

namespace Centrex\Crm\Commands;

use Illuminate\Console\Command;

class CrmCommand extends Command
{
    public $signature = 'laravel-crm';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
