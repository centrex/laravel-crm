<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'full_name'   => $this->full_name,
            'first_name'  => $this->first_name,
            'last_name'   => $this->last_name,
            'email'       => $this->email,
            'phone'       => $this->phone,
            'job_title'   => $this->job_title,
            'owner_id'    => $this->owner_id,
            'is_primary'  => $this->is_primary,
            'meta'        => $this->meta,
            'company'     => new CompanyResource($this->whenLoaded('company')),
            'tags'        => TagResource::collection($this->whenLoaded('tags')),
            'leads'       => LeadResource::collection($this->whenLoaded('leads')),
            'deals'       => DealResource::collection($this->whenLoaded('deals')),
            'clv'         => new ClvSnapshotResource($this->whenLoaded('latestClvSnapshot')),
            'clv_history' => ClvSnapshotResource::collection($this->whenLoaded('clvSnapshots')),
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}
