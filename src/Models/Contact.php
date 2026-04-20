<?php

declare(strict_types = 1);

namespace Centrex\Crm\Models;

use Centrex\Crm\Concerns\{AddTablePrefix, HasActivities, HasTags};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};

class Contact extends Model
{
    use AddTablePrefix;
    use HasActivities;
    use HasFactory;
    use HasTags;
    use SoftDeletes;

    protected function getTableSuffix(): string
    {
        return 'contacts';
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setConnection(config('crm.drivers.database.connection', config('database.default')));
    }

    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'job_title',
        'owner_id',
        'is_primary',
        'meta',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'meta'       => 'array',
    ];

    protected $appends = [
        'full_name',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function latestClvSnapshot(): HasOne
    {
        return $this->hasOne(ClvSnapshot::class)->latestOfMany('calculated_at');
    }

    public function clvSnapshots(): HasMany
    {
        return $this->hasMany(ClvSnapshot::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(sprintf('%s %s', $this->first_name, $this->last_name));
    }

    public function wonDealsAsTransactions(): array
    {
        return $this->deals()
            ->where('stage', 'won')
            ->whereNotNull('won_at')
            ->get(['won_at', 'amount'])
            ->map(static fn (Deal $deal): array => [
                'date'   => $deal->won_at,
                'amount' => (float) $deal->amount,
            ])
            ->all();
    }
}
