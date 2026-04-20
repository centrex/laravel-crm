<?php

declare(strict_types = 1);

namespace Centrex\Crm\Enums;

enum ActivityPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';
}
