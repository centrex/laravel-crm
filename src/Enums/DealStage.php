<?php

declare(strict_types = 1);

namespace Centrex\Crm\Enums;

enum DealStage: string
{
    case Qualified = 'qualified';
    case Proposal = 'proposal';
    case Negotiation = 'negotiation';
    case Won = 'won';
    case Lost = 'lost';

    public function isClosed(): bool
    {
        return in_array($this, [self::Won, self::Lost], true);
    }
}
