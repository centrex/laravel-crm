<?php

declare(strict_types = 1);

return [
    'table_prefix' => 'crm_',

    'drivers' => [
        'database' => [
            'connection' => env('CRM_DB_CONNECTION', env('DB_CONNECTION', 'sqlite')),
        ],
    ],

    'web_enabled' => env('CRM_WEB_ENABLED', true),

    'web_prefix' => env('CRM_WEB_PREFIX', 'crm'),

    'web_middleware' => ['web', 'auth'],

    'api_enabled' => env('CRM_API_ENABLED', true),

    'api_prefix' => env('CRM_API_PREFIX', 'api/crm'),

    'api_middleware' => ['api', 'auth:sanctum'],

    'user_foreign_keys' => env('CRM_USER_FOREIGN_KEYS', false),

    'admin_role_attribute' => env('CRM_ADMIN_ROLE_ATTRIBUTE'),

    'admin_roles' => array_values(array_filter(array_map(
        static fn (string $role): string => trim($role),
        explode(',', (string) env('CRM_ADMIN_ROLES', 'admin,crm-admin')),
    ))),

    'clv_horizon_months' => env('CRM_CLV_HORIZON_MONTHS', 12),

    'clv_discount_rate' => env('CRM_CLV_DISCOUNT_RATE', 0.1),
];
