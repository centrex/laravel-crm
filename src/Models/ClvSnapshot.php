<?php

declare(strict_types = 1);

namespace Centrex\Crm\Models;

use Centrex\Crm\Concerns\AddTablePrefix;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClvSnapshot extends Model
{
    use AddTablePrefix;

    protected function getTableSuffix(): string
    {
        return 'clv_snapshots';
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setConnection(config('crm.drivers.database.connection', config('database.default')));
    }

    protected $fillable = [
        'contact_id',
        'horizon_months',
        'clv_value',
        'expected_monthly_value',
        'avg_deal_value',
        'total_revenue',
        'frequency',
        'recency_days',
        'age_days',
        'p_alive',
        'expected_transactions',
        'calculated_at',
        'meta',
    ];

    protected $casts = [
        'clv_value'              => 'decimal:2',
        'expected_monthly_value' => 'decimal:2',
        'avg_deal_value'         => 'decimal:2',
        'total_revenue'          => 'decimal:2',
        'frequency'              => 'integer',
        'recency_days'           => 'float',
        'age_days'               => 'float',
        'p_alive'                => 'float',
        'expected_transactions'  => 'float',
        'calculated_at'          => 'datetime',
        'meta'                   => 'array',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
