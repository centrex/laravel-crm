<?php

declare(strict_types = 1);

namespace Centrex\Crm\Services;

use Centrex\Crm\Enums\{ActivityType, WhatsappMessageType};
use Centrex\Crm\Models\{Contact, WhatsappMessage, WhatsappTemplate};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WhatsappService
{
    private const string WA_BASE_URL = 'https://wa.me/';

    /**
     * Build a wa.me deep-link that opens WhatsApp Web with the message pre-filled.
     * Works on desktop (WhatsApp Web) and mobile (WhatsApp app).
     */
    public function buildWaUrl(string $phone, string $message): string
    {
        $normalized = $this->normalizePhone($phone);

        return self::WA_BASE_URL . $normalized . '?text=' . rawurlencode($message);
    }

    /**
     * Strip non-digits; keep a leading '+' so international numbers are preserved.
     * E.g. "+880 1712-345678" → "8801712345678"
     */
    public function normalizePhone(string $phone): string
    {
        return preg_replace('/\D/', '', $phone) ?? '';
    }

    /**
     * Render a raw message body by substituting {{placeholder}} tokens.
     *
     * Available tokens: {{name}}, {{company}}, {{product}}, {{offer}}, {{price}},
     * {{message}}, {{sender}}. Any extra keys in $vars are also replaced.
     */
    public function renderMessage(string $body, array $vars = []): string
    {
        foreach ($vars as $key => $value) {
            $body = str_replace('{{' . $key . '}}', (string) $value, $body);
        }

        return $body;
    }

    /**
     * Render a WhatsappTemplate for a specific Contact and optional extra vars.
     */
    public function renderTemplate(WhatsappTemplate $template, Contact $contact, array $extraVars = []): string
    {
        $vars = array_merge([
            'name'    => $contact->full_name,
            'company' => $contact->company?->name ?? '',
        ], $extraVars);

        return $template->render($vars);
    }

    /**
     * Create a single WhatsappMessage record, generate the wa.me URL, and log
     * a CRM activity on the contact.
     */
    public function createMessage(
        string $phone,
        string $messageBody,
        WhatsappMessageType $type,
        ?int $contactId = null,
        ?int $templateId = null,
        ?int $sentBy = null,
    ): WhatsappMessage {
        $waUrl = $this->buildWaUrl($phone, $messageBody);

        return DB::connection(config('crm.drivers.database.connection', config('database.default')))
            ->transaction(function () use ($phone, $messageBody, $type, $contactId, $templateId, $sentBy, $waUrl): WhatsappMessage {
                $message = WhatsappMessage::query()->create([
                    'template_id'  => $templateId,
                    'contact_id'   => $contactId,
                    'phone'        => $phone,
                    'type'         => $type->value,
                    'message_body' => $messageBody,
                    'wa_url'       => $waUrl,
                    'status'       => 'pending',
                    'sent_by'      => $sentBy,
                ]);

                if ($contactId !== null) {
                    $contact = Contact::query()->find($contactId);

                    if ($contact !== null) {
                        $contact->morphMany(\Centrex\Crm\Models\Activity::class, 'subject')->create([
                            'type'        => ActivityType::Whatsapp->value,
                            'priority'    => 'normal',
                            'summary'     => 'WhatsApp message sent — ' . $type->label(),
                            'description' => mb_substr($messageBody, 0, 500),
                            'owner_id'    => $sentBy,
                            'meta'        => ['wa_message_id' => $message->id, 'phone' => $phone],
                        ]);
                    }
                }

                return $message;
            });
    }

    /**
     * Prepare WhatsappMessages in bulk for a list of contacts.
     * Returns a Collection of WhatsappMessage models (each with a ready wa_url).
     *
     * @param  array<int>  $contactIds
     * @return Collection<int, WhatsappMessage>
     */
    public function bulkPrepare(
        array $contactIds,
        string $rawMessageBody,
        WhatsappMessageType $type,
        ?int $templateId = null,
        ?int $sentBy = null,
    ): Collection {
        $contacts = Contact::query()->with('company')->findMany($contactIds);

        $messages = collect();

        foreach ($contacts as $contact) {
            $phone = $contact->phone ?? '';

            if ($phone === '') {
                continue;
            }

            $rendered = $this->renderMessage($rawMessageBody, [
                'name'    => $contact->full_name,
                'company' => $contact->company?->name ?? '',
            ]);

            $messages->push($this->createMessage(
                phone: $phone,
                messageBody: $rendered,
                type: $type,
                contactId: $contact->id,
                templateId: $templateId,
                sentBy: $sentBy,
            ));
        }

        return $messages;
    }

    /**
     * Mark a message as opened (wa.me link was clicked).
     */
    public function markOpened(WhatsappMessage $message): WhatsappMessage
    {
        return $message->markOpened();
    }

    /**
     * List all active templates, optionally filtered by type.
     *
     * @return Collection<int, WhatsappTemplate>
     */
    public function templates(?WhatsappMessageType $type = null): Collection
    {
        $query = WhatsappTemplate::query()->where('is_active', true)->orderBy('name');

        if ($type !== null) {
            $query->where('type', $type->value);
        }

        return $query->get();
    }

    /**
     * Create or update a message template.
     */
    public function saveTemplate(array $attributes): WhatsappTemplate
    {
        $id = $attributes['id'] ?? null;

        if ($id !== null) {
            /** @var WhatsappTemplate $template */
            $template = WhatsappTemplate::query()->findOrFail($id);
            $template->update($attributes);

            return $template->refresh();
        }

        return WhatsappTemplate::query()->create($attributes);
    }
}
