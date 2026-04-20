<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'code'                => $this->code,
            'name'                => $this->name,
            'stage'               => $this->stage?->value,
            'amount'              => (float) $this->amount,
            'currency'            => $this->currency,
            'probability'         => $this->probability,
            'expected_close_date' => $this->expected_close_date?->toDateString(),
            'owner_id'            => $this->owner_id,
            'won_at'              => $this->won_at?->toISOString(),
            'lost_at'             => $this->lost_at?->toISOString(),
            'is_closed'           => $this->isClosed(),
            'notes'               => $this->notes,
            'meta'                => $this->meta,
            'company'             => new CompanyResource($this->whenLoaded('company')),
            'contact'             => new ContactResource($this->whenLoaded('contact')),
            'lead'                => new LeadResource($this->whenLoaded('lead')),
            'tags'                => TagResource::collection($this->whenLoaded('tags')),
            'activities'          => ActivityResource::collection($this->whenLoaded('activities')),
            'created_at'          => $this->created_at?->toISOString(),
            'updated_at'          => $this->updated_at?->toISOString(),
        ];
    }
}
