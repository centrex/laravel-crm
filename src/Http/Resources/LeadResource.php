<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'code'              => $this->code,
            'title'             => $this->title,
            'source'            => $this->source?->value,
            'status'            => $this->status?->value,
            'value'             => (float) $this->value,
            'currency'          => $this->currency,
            'probability'       => $this->probability,
            'score'             => $this->score,
            'owner_id'          => $this->owner_id,
            'next_follow_up_at' => $this->next_follow_up_at?->toISOString(),
            'qualified_at'      => $this->qualified_at?->toISOString(),
            'lost_at'           => $this->lost_at?->toISOString(),
            'notes'             => $this->notes,
            'meta'              => $this->meta,
            'company'           => new CompanyResource($this->whenLoaded('company')),
            'contact'           => new ContactResource($this->whenLoaded('contact')),
            'tags'              => TagResource::collection($this->whenLoaded('tags')),
            'activities'        => ActivityResource::collection($this->whenLoaded('activities')),
            'deals'             => DealResource::collection($this->whenLoaded('deals')),
            'created_at'        => $this->created_at?->toISOString(),
            'updated_at'        => $this->updated_at?->toISOString(),
        ];
    }
}
