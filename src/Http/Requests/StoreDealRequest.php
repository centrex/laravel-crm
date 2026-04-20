<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Requests;

use Centrex\Crm\Enums\DealStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDealRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'lead_id'             => ['nullable', 'integer'],
            'company_id'          => ['nullable', 'integer'],
            'contact_id'          => ['nullable', 'integer'],
            'name'                => ['required', 'string', 'max:255'],
            'stage'               => ['nullable', new Enum(DealStage::class)],
            'amount'              => ['nullable', 'numeric', 'min:0'],
            'currency'            => ['nullable', 'string', 'size:3'],
            'probability'         => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
            'owner_id'            => ['nullable', 'integer'],
            'notes'               => ['nullable', 'string'],
            'meta'                => ['nullable', 'array'],
            'tags'                => ['nullable', 'array'],
            'tags.*'              => ['string'],
        ];
    }
}
