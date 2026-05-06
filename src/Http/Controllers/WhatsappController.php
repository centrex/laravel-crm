<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Controllers;

use Centrex\Crm\Enums\WhatsappMessageType;
use Centrex\Crm\Http\Requests\{SendWhatsappRequest, StoreWhatsappTemplateRequest};
use Centrex\Crm\Models\{Contact, WhatsappMessage, WhatsappTemplate};
use Centrex\Crm\Services\WhatsappService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Routing\Controller;

class WhatsappController extends Controller
{
    public function __construct(private readonly WhatsappService $whatsapp) {}

    /** Compose form — pick contacts, choose template, preview message. */
    public function compose(Request $request): View
    {
        $contacts = Contact::query()->with('company')->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'phone', 'company_id']);
        $templates = WhatsappTemplate::query()->where('is_active', true)->orderBy('name')->get();
        $types = WhatsappMessageType::cases();

        $preselected = $request->query('contact_id');

        return view('crm::whatsapp.compose', compact('contacts', 'templates', 'types', 'preselected'));
    }

    /** Generate wa.me links for each selected contact and store message records. */
    public function send(SendWhatsappRequest $request): RedirectResponse
    {
        $type = WhatsappMessageType::from($request->validated('type'));

        $messages = $this->whatsapp->bulkPrepare(
            contactIds: array_map('intval', $request->validated('contact_ids')),
            rawMessageBody: $request->validated('message_body'),
            type: $type,
            templateId: $request->validated('template_id') ? (int) $request->validated('template_id') : null,
            sentBy: auth()->id(),
        );

        return redirect()
            ->route('crm.whatsapp.history')
            ->with('crm_success', "WhatsApp links generated for {$messages->count()} contact(s).")
            ->with('crm_wa_message_ids', $messages->pluck('id')->toArray());
    }

    /** Message history — list of all generated wa.me records. */
    public function history(Request $request): View
    {
        $messages = WhatsappMessage::query()
            ->with(['contact.company', 'template'])
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('crm::whatsapp.history', compact('messages'));
    }

    /** Template list + create form. */
    public function templates(): View
    {
        $templates = WhatsappTemplate::query()->orderBy('name')->paginate(20);
        $types = WhatsappMessageType::cases();

        return view('crm::whatsapp.templates', compact('templates', 'types'));
    }

    /** Store a new template. */
    public function storeTemplate(StoreWhatsappTemplateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $this->whatsapp->saveTemplate($data);

        return back()->with('crm_success', 'WhatsApp template saved.');
    }

    /** Soft-toggle template active flag. */
    public function toggleTemplate(WhatsappTemplate $template): RedirectResponse
    {
        $template->update(['is_active' => !$template->is_active]);

        return back()->with('crm_success', 'Template updated.');
    }

    /** Mark a message as opened (called when user clicks a wa.me link). */
    public function markOpened(WhatsappMessage $message): RedirectResponse
    {
        $this->whatsapp->markOpened($message);

        return redirect()->away($message->wa_url);
    }
}
