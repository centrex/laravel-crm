<?php

declare(strict_types = 1);

namespace Centrex\Crm\Enums;

enum LeadStatus: string
{
    case Open = 'open';
    case Qualified = 'qualified';
    case Lost = 'lost';
}
