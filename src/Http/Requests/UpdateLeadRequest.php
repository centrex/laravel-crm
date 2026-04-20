<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Requests;

use Centrex\Crm\Enums\{LeadSource, LeadStatus};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateLeadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_id'       => ['nullable', 'integer'],
            'contact_id'       => ['nullable', 'integer'],
            'title'            => ['sometimes', 'string', 'max:255'],
            'source'           => ['nullable', new Enum(LeadSource::class)],
            'status'           => ['nullable', new Enum(LeadStatus::class)],
            'value'            => ['nullable', 'numeric', 'min:0'],
            'currency'         => ['nullable', 'string', 'size:3'],
            'probability'      => ['nullable', 'integer', 'min:0', 'max:100'],
            'owner_id'         => ['nullable', 'integer'],
            'next_follow_up_at' => ['nullable', 'date'],
            'notes'            => ['nullable', 'string'],
            'meta'             => ['nullable', 'array'],
            'tags'             => ['nullable', 'array'],
            'tags.*'           => ['string'],
        ];
    }
}
