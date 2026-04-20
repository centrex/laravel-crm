<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_id' => ['nullable', 'integer'],
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name'  => ['nullable', 'string', 'max:100'],
            'email'      => ['nullable', 'email', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'job_title'  => ['nullable', 'string', 'max:150'],
            'owner_id'   => ['nullable', 'integer'],
            'is_primary' => ['nullable', 'boolean'],
            'meta'       => ['nullable', 'array'],
            'tags'       => ['nullable', 'array'],
            'tags.*'     => ['string'],
        ];
    }
}
