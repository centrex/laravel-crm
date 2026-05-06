<?php

declare(strict_types = 1);

namespace Centrex\Crm\Models;

use Centrex\Crm\Concerns\AddTablePrefix;
use Centrex\Crm\Enums\WhatsappMessageType;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class WhatsappMessage extends Model
{
    use AddTablePrefix;

    #[\Override]
    protected function getTableSuffix(): string
    {
        return 'whatsapp_messages';
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setConnection(config('crm.drivers.database.connection', config('database.default')));
    }

    protected $fillable = [
        'template_id',
        'contact_id',
        'phone',
        'type',
        'message_body',
        'wa_url',
        'status',
        'sent_by',
        'opened_at',
    ];

    protected $casts = [
        'type'      => WhatsappMessageType::class,
        'opened_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(WhatsappTemplate::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isOpened(): bool
    {
        return $this->status === 'opened';
    }

    public function markOpened(): static
    {
        $this->forceFill(['status' => 'opened', 'opened_at' => now()])->save();

        return $this->refresh();
    }
}
