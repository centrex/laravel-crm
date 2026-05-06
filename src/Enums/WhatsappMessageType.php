<?php

declare(strict_types = 1);

namespace Centrex\Crm\Enums;

enum WhatsappMessageType: string
{
    case ProductUpdate = 'product_update';
    case Offer = 'offer';
    case FollowUp = 'follow_up';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::ProductUpdate => 'Product Update',
            self::Offer         => 'Offer / Promotion',
            self::FollowUp      => 'Follow-Up',
            self::Custom        => 'Custom Message',
        };
    }

    public function defaultTemplate(): string
    {
        return match ($this) {
            self::ProductUpdate => "Hi {{name}},\n\nWe have exciting updates on *{{product}}*! 🎉\n\n{{message}}\n\nFeel free to reach out if you'd like to know more.\n\nRegards,\n{{sender}}",
            self::Offer         => "Hi {{name}},\n\nWe have a special offer just for you! 🎁\n\n*{{offer}}*\n\nPrice: {{price}}\n\nThis offer is valid for a limited time. Don't miss out!\n\nRegards,\n{{sender}}",
            self::FollowUp      => "Hi {{name}},\n\nJust following up on our recent conversation. I wanted to check in and see if you have any questions.\n\n{{message}}\n\nLooking forward to hearing from you!\n\nRegards,\n{{sender}}",
            self::Custom        => "Hi {{name}},\n\n{{message}}\n\nRegards,\n{{sender}}",
        };
    }
}
