<?php

declare(strict_types = 1);

namespace Centrex\Crm\Models;

use Centrex\Crm\Concerns\AddTablePrefix;
use Centrex\Crm\Enums\ActivityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use AddTablePrefix;

    protected function getTableSuffix(): string
    {
        return 'activities';
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setConnection(config('crm.drivers.database.connection', config('database.default')));
    }

    protected $fillable = [
        'type',
        'summary',
        'description',
        'due_at',
        'completed_at',
        'owner_id',
        'meta',
    ];

    protected $casts = [
        'type'         => ActivityType::class,
        'due_at'       => 'datetime',
        'completed_at' => 'datetime',
        'meta'         => 'array',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
