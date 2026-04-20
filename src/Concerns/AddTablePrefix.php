<?php

declare(strict_types = 1);

namespace Centrex\Crm\Concerns;

trait AddTablePrefix
{
    public function getTable(): string
    {
        $prefix = config('crm.table_prefix', 'crm_');

        return $prefix . $this->getTableSuffix();
    }

    abstract protected function getTableSuffix(): string;
}
