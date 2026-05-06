<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Requests;

use Centrex\Crm\Enums\WhatsappMessageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWhatsappTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'type'         => ['required', Rule::enum(WhatsappMessageType::class)],
            'message_body' => ['required', 'string', 'min:1', 'max:4096'],
            'is_active'    => ['nullable', 'boolean'],
        ];
    }
}
