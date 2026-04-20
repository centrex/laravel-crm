<?php

declare(strict_types = 1);

namespace Centrex\Crm\Enums;

enum ActivityType: string
{
    case Call = 'call';
    case Email = 'email';
    case Meeting = 'meeting';
    case Task = 'task';
    case Note = 'note';
}
