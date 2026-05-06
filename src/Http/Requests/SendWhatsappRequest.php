<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Requests;

use Centrex\Crm\Enums\WhatsappMessageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendWhatsappRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'contact_ids'   => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['integer', 'min:1'],
            'template_id'   => ['nullable', 'integer', 'min:1'],
            'type'          => ['required', Rule::enum(WhatsappMessageType::class)],
            'message_body'  => ['required', 'string', 'min:1', 'max:4096'],
        ];
    }
}
