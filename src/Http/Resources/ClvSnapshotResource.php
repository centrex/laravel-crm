<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClvSnapshotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'contact_id'             => $this->contact_id,
            'horizon_months'         => $this->horizon_months,
            'clv_value'              => (float) $this->clv_value,
            'expected_monthly_value' => (float) $this->expected_monthly_value,
            'avg_deal_value'         => (float) $this->avg_deal_value,
            'total_revenue'          => (float) $this->total_revenue,
            'frequency'              => $this->frequency,
            'recency_days'           => (float) $this->recency_days,
            'age_days'               => (float) $this->age_days,
            'p_alive'                => (float) $this->p_alive,
            'expected_transactions'  => (float) $this->expected_transactions,
            'calculated_at'          => $this->calculated_at?->toISOString(),
            'meta'                   => $this->meta,
        ];
    }
}
