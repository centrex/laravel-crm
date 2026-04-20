<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Requests;

use Centrex\Crm\Enums\{ActivityPriority, ActivityType};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreActivityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'subject_type' => ['nullable', 'string'],
            'subject_id'   => ['nullable', 'integer'],
            'type'         => ['required', new Enum(ActivityType::class)],
            'priority'     => ['nullable', new Enum(ActivityPriority::class)],
            'summary'      => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'due_at'       => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'owner_id'     => ['nullable', 'integer'],
            'meta'         => ['nullable', 'array'],
        ];
    }
}
