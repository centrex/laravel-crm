<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'type'         => $this->type?->value,
            'priority'     => $this->priority?->value,
            'summary'      => $this->summary,
            'description'  => $this->description,
            'due_at'       => $this->due_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'is_completed' => $this->isCompleted(),
            'is_overdue'   => $this->isOverdue(),
            'owner_id'     => $this->owner_id,
            'meta'         => $this->meta,
            'subject_type' => $this->subject_type,
            'subject_id'   => $this->subject_id,
            'created_at'   => $this->created_at?->toISOString(),
            'updated_at'   => $this->updated_at?->toISOString(),
        ];
    }
}
