<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['nullable', 'email', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:50'],
            'website'   => ['nullable', 'url', 'max:255'],
            'industry'  => ['nullable', 'string', 'max:100'],
            'owner_id'  => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'meta'      => ['nullable', 'array'],
            'tags'      => ['nullable', 'array'],
            'tags.*'    => ['string'],
        ];
    }
}
