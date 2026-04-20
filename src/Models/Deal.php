<?php

declare(strict_types = 1);

namespace Centrex\Crm\Models;

use Centrex\Crm\Concerns\AddTablePrefix;
use Centrex\Crm\Enums\DealStage;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphMany};

class Deal extends Model
{
    use AddTablePrefix;
    use SoftDeletes;

    protected function getTableSuffix(): string
    {
        return 'deals';
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setConnection(config('crm.drivers.database.connection', config('database.default')));
    }

    protected $fillable = [
        'code',
        'lead_id',
        'company_id',
        'contact_id',
        'name',
        'stage',
        'amount',
        'currency',
        'probability',
        'expected_close_date',
        'owner_id',
        'won_at',
        'lost_at',
        'notes',
        'meta',
    ];

    protected $casts = [
        'amount'              => 'decimal:2',
        'probability'         => 'integer',
        'expected_close_date' => 'date',
        'won_at'              => 'datetime',
        'lost_at'             => 'datetime',
        'meta'                => 'array',
        'stage'               => DealStage::class,
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function isClosed(): bool
    {
        return $this->stage->isClosed();
    }
}
