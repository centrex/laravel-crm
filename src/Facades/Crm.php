<?php

declare(strict_types = 1);

namespace Centrex\Crm\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Centrex\Crm\Crm
 */
class Crm extends Facade
{
    #[\Override]
    protected static function getFacadeAccessor()
    {
        return 'crm';
    }
}
