<?php

declare(strict_types = 1);

namespace Centrex\Crm\Models;

use Centrex\Crm\Concerns\AddTablePrefix;
use Centrex\Crm\Enums\WhatsappMessageType;
use Illuminate\Database\Eloquent\{Model, Relations\HasMany};

class WhatsappTemplate extends Model
{
    use AddTablePrefix;

    #[\Override]
    protected function getTableSuffix(): string
    {
        return 'whatsapp_templates';
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setConnection(config('crm.drivers.database.connection', config('database.default')));
    }

    protected $fillable = [
        'name',
        'type',
        'message_body',
        'is_active',
    ];

    protected $casts = [
        'type'      => WhatsappMessageType::class,
        'is_active' => 'boolean',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class, 'template_id');
    }

    /** Replace placeholders in the body with supplied variables. */
    public function render(array $variables = []): string
    {
        $body = $this->message_body;

        foreach ($variables as $key => $value) {
            $body = str_replace('{{' . $key . '}}', (string) $value, $body);
        }

        return $body;
    }
}
