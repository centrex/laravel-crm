<?php

declare(strict_types = 1);

namespace Centrex\Crm\Enums;

enum LeadSource: string
{
    case Web = 'web';
    case Referral = 'referral';
    case ColdCall = 'cold_call';
    case Advertisement = 'advertisement';
    case SocialMedia = 'social_media';
    case Event = 'event';
    case Partner = 'partner';
    case Other = 'other';
}
