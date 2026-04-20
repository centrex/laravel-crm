<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Controllers\Api;

use Centrex\Crm\Crm;
use Centrex\Crm\Http\Requests\{StoreLeadRequest, UpdateLeadRequest};
use Centrex\Crm\Http\Resources\{DealResource, LeadResource};
use Centrex\Crm\Models\Lead;
use Illuminate\Http\{JsonResponse, Request, Resources\Json\AnonymousResourceCollection};
use Illuminate\Routing\Controller;

class LeadController extends Controller
{
    public function __construct(private readonly Crm $crm) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Lead::query()->with(['company', 'contact', 'tags']);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->value());
        }

        if ($request->filled('source')) {
            $query->where('source', $request->string('source')->value());
        }

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->integer('owner_id'));
        }

        return LeadResource::collection(
            $query->orderByDesc('created_at')->paginate($request->integer('per_page', 15)),
        );
    }

    public function store(StoreLeadRequest $request): LeadResource
    {
        $lead = $this->crm->createLead($request->validated());

        if ($request->filled('tags')) {
            $lead->syncTags($request->array('tags'));
        }

        return new LeadResource($lead->load(['company', 'contact', 'tags']));
    }

    public function show(Lead $lead): LeadResource
    {
        return new LeadResource($lead->load(['company', 'contact', 'tags', 'activities', 'deals']));
    }

    public function update(UpdateLeadRequest $request, Lead $lead): LeadResource
    {
        $lead->update($request->validated());

        if ($request->has('tags')) {
            $lead->syncTags($request->array('tags'));
        }

        return new LeadResource($lead->fresh(['company', 'contact', 'tags']));
    }

    public function destroy(Lead $lead): JsonResponse
    {
        $lead->delete();

        return response()->json(null, 204);
    }

    public function qualify(Request $request, Lead $lead): DealResource
    {
        $deal = $this->crm->qualifyLead($lead, $request->validated([
            'name', 'amount', 'currency', 'probability', 'expected_close_date', 'owner_id', 'notes',
        ]));

        return new DealResource($deal->load(['company', 'contact', 'lead']));
    }

    public function markLost(Request $request, Lead $lead): LeadResource
    {
        $updated = $this->crm->markLeadLost($lead, $request->string('reason')->value() ?: null);

        return new LeadResource($updated);
    }
}
