<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'code'       => $this->code,
            'name'       => $this->name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'website'    => $this->website,
            'industry'   => $this->industry,
            'owner_id'   => $this->owner_id,
            'is_active'  => $this->is_active,
            'meta'       => $this->meta,
            'tags'       => TagResource::collection($this->whenLoaded('tags')),
            'contacts'   => ContactResource::collection($this->whenLoaded('contacts')),
            'leads'      => LeadResource::collection($this->whenLoaded('leads')),
            'deals'      => DealResource::collection($this->whenLoaded('deals')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
