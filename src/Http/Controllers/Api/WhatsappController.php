<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Controllers\Api;

use Centrex\Crm\Enums\WhatsappMessageType;
use Centrex\Crm\Http\Requests\{SendWhatsappRequest, StoreWhatsappTemplateRequest};
use Centrex\Crm\Models\{WhatsappMessage, WhatsappTemplate};
use Centrex\Crm\Services\WhatsappService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Routing\Controller;

class WhatsappController extends Controller
{
    public function __construct(private readonly WhatsappService $whatsapp) {}

    /**
     * POST /api/crm/whatsapp/send
     * Generate wa.me links for one or many contacts.
     */
    public function send(SendWhatsappRequest $request): JsonResponse
    {
        $type = WhatsappMessageType::from($request->validated('type'));

        $messages = $this->whatsapp->bulkPrepare(
            contactIds: array_map('intval', $request->validated('contact_ids')),
            rawMessageBody: $request->validated('message_body'),
            type: $type,
            templateId: $request->validated('template_id') ? (int) $request->validated('template_id') : null,
            sentBy: auth()->id(),
        );

        return response()->json([
            'count'    => $messages->count(),
            'messages' => $messages->map(fn (WhatsappMessage $m): array => [
                'id'           => $m->id,
                'contact_id'   => $m->contact_id,
                'phone'        => $m->phone,
                'wa_url'       => $m->wa_url,
                'status'       => $m->status,
                'message_body' => $m->message_body,
            ])->values(),
        ], 201);
    }

    /**
     * GET /api/crm/whatsapp/messages
     * List message history with optional filters.
     */
    public function messages(Request $request): JsonResponse
    {
        $query = WhatsappMessage::query()->with(['contact', 'template'])->orderByDesc('created_at');

        if ($request->has('contact_id')) {
            $query->where('contact_id', (int) $request->query('contact_id'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json($query->paginate(30));
    }

    /**
     * POST /api/crm/whatsapp/messages/{message}/open
     * Mark a generated message as opened (wa.me link clicked).
     */
    public function markOpened(WhatsappMessage $message): JsonResponse
    {
        $this->whatsapp->markOpened($message);

        return response()->json(['status' => $message->fresh()?->status, 'opened_at' => $message->fresh()?->opened_at]);
    }

    /**
     * GET /api/crm/whatsapp/templates
     * List active templates (optionally filtered by type).
     */
    public function listTemplates(Request $request): JsonResponse
    {
        $type = $request->has('type') ? WhatsappMessageType::from((string) $request->query('type')) : null;

        return response()->json($this->whatsapp->templates($type)->values());
    }

    /**
     * POST /api/crm/whatsapp/templates
     * Create a new message template.
     */
    public function storeTemplate(StoreWhatsappTemplateRequest $request): JsonResponse
    {
        $data              = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $template = $this->whatsapp->saveTemplate($data);

        return response()->json($template, 201);
    }

    /**
     * PUT /api/crm/whatsapp/templates/{template}
     * Update an existing template.
     */
    public function updateTemplate(StoreWhatsappTemplateRequest $request, WhatsappTemplate $template): JsonResponse
    {
        $data              = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? $template->is_active);
        $data['id']        = $template->id;

        $template = $this->whatsapp->saveTemplate($data);

        return response()->json($template);
    }

    /**
     * DELETE /api/crm/whatsapp/templates/{template}
     */
    public function destroyTemplate(WhatsappTemplate $template): JsonResponse
    {
        $template->delete();

        return response()->json(null, 204);
    }
}
