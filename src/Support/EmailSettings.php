<?php

declare(strict_types = 1);

namespace Centrex\Crm\Support;

use Centrex\Crm\Models\EmailSetting;
use Illuminate\Support\Facades\{Cache, Config, Schema};

final class EmailSettings
{
    private const CACHE_KEY = 'crm.email_settings';

    /** @return array<string, mixed> */
    public static function data(): array
    {
        $stored = self::stored();

        return [
            'enabled'             => filter_var($stored['enabled'] ?? config('crm.email.enabled', true), FILTER_VALIDATE_BOOL),
            'from_address'        => self::normalizeEmail($stored['from_address'] ?? config('crm.email.from_address', config('mail.from.address', 'hello@example.com'))),
            'from_name'           => self::normalizeText($stored['from_name'] ?? config('crm.email.from_name', config('mail.from.name', 'CRM')), 'CRM'),
            'reply_to'            => self::normalizeEmail($stored['reply_to'] ?? config('crm.email.reply_to', config('mail.from.address', 'hello@example.com'))),
            'default_owner_email' => self::normalizeEmail($stored['default_owner_email'] ?? config('crm.email.default_owner_email', config('mail.from.address', 'hello@example.com'))),
            'lead_subject'        => self::normalizeText($stored['lead_subject'] ?? config('crm.email.lead_subject', 'New CRM lead assigned'), 'New CRM lead assigned'),
            'deal_subject'        => self::normalizeText($stored['deal_subject'] ?? config('crm.email.deal_subject', 'CRM deal update'), 'CRM deal update'),
            'activity_subject'    => self::normalizeText($stored['activity_subject'] ?? config('crm.email.activity_subject', 'CRM activity reminder'), 'CRM activity reminder'),
        ];
    }

    /** @param array<string, mixed> $data */
    public static function update(array $data): void
    {
        if (!self::tableExists()) {
            return;
        }

        $settings = [
            'enabled'             => filter_var($data['enabled'] ?? false, FILTER_VALIDATE_BOOL),
            'from_address'        => self::normalizeEmail($data['from_address'] ?? null),
            'from_name'           => self::normalizeText($data['from_name'] ?? null, 'CRM'),
            'reply_to'            => self::normalizeEmail($data['reply_to'] ?? null),
            'default_owner_email' => self::normalizeEmail($data['default_owner_email'] ?? null),
            'lead_subject'        => self::normalizeText($data['lead_subject'] ?? null, 'New CRM lead assigned'),
            'deal_subject'        => self::normalizeText($data['deal_subject'] ?? null, 'CRM deal update'),
            'activity_subject'    => self::normalizeText($data['activity_subject'] ?? null, 'CRM activity reminder'),
        ];

        foreach ($settings as $key => $value) {
            EmailSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)],
            );
        }

        Cache::forget(self::CACHE_KEY);
        self::apply();
    }

    public static function apply(): void
    {
        $settings = self::data();

        Config::set('crm.email', $settings);
    }

    /** @return array<string, mixed> */
    private static function stored(): array
    {
        if (!self::tableExists()) {
            return [];
        }

        return Cache::rememberForever(self::CACHE_KEY, static fn (): array => EmailSetting::query()
            ->pluck('value', 'key')
            ->map(static function (?string $value): mixed {
                if ($value === null) {
                    return null;
                }

                $decoded = json_decode($value, true);

                return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
            })
            ->all());
    }

    private static function tableExists(): bool
    {
        try {
            return Schema::connection(config('crm.drivers.database.connection', config('database.default')))
                ->hasTable(config('crm.table_prefix', 'crm_') . 'email_settings');
        } catch (\Throwable) {
            return false;
        }
    }

    private static function normalizeEmail(mixed $value): string
    {
        $normalized = strtolower(trim((string) $value));

        return filter_var($normalized, FILTER_VALIDATE_EMAIL) ? $normalized : 'hello@example.com';
    }

    private static function normalizeText(mixed $value, string $fallback): string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? $fallback : $normalized;
    }
}
