<?php

declare(strict_types = 1);

namespace Centrex\Crm\Models;

use Centrex\Crm\Concerns\{AddTablePrefix, HasActivities, HasTags};
use Centrex\Crm\Enums\{LeadSource, LeadStatus};
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Lead extends Model
{
    use AddTablePrefix;
    use HasActivities;
    use HasTags;
    use SoftDeletes;

    protected function getTableSuffix(): string
    {
        return 'leads';
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setConnection(config('crm.drivers.database.connection', config('database.default')));
    }

    protected $fillable = [
        'code',
        'company_id',
        'contact_id',
        'title',
        'source',
        'value',
        'currency',
        'status',
        'probability',
        'score',
        'owner_id',
        'next_follow_up_at',
        'qualified_at',
        'lost_at',
        'notes',
        'meta',
    ];

    protected $casts = [
        'value'             => 'decimal:2',
        'probability'       => 'integer',
        'score'             => 'integer',
        'next_follow_up_at' => 'datetime',
        'qualified_at'      => 'datetime',
        'lost_at'           => 'datetime',
        'meta'              => 'array',
        'status'            => LeadStatus::class,
        'source'            => LeadSource::class,
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }
}
