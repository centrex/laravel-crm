<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Controllers\Api;

use Centrex\Crm\Crm;
use Centrex\Crm\Enums\DealStage;
use Centrex\Crm\Http\Requests\{StoreDealRequest, UpdateDealRequest};
use Centrex\Crm\Http\Resources\DealResource;
use Centrex\Crm\Models\Deal;
use Illuminate\Http\{JsonResponse, Request, Resources\Json\AnonymousResourceCollection};
use Illuminate\Routing\Controller;

class DealController extends Controller
{
    public function __construct(private readonly Crm $crm) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Deal::query()->with(['company', 'contact', 'lead', 'tags']);

        if ($request->filled('stage')) {
            $query->where('stage', $request->string('stage')->value());
        }

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->integer('owner_id'));
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->integer('company_id'));
        }

        return DealResource::collection(
            $query->orderByDesc('created_at')->paginate($request->integer('per_page', 15)),
        );
    }

    public function store(StoreDealRequest $request): DealResource
    {
        $deal = Deal::query()->create($request->validated());

        if ($request->filled('tags')) {
            $deal->syncTags($request->array('tags'));
        }

        return new DealResource($deal->load(['company', 'contact', 'lead', 'tags']));
    }

    public function show(Deal $deal): DealResource
    {
        return new DealResource($deal->load(['company', 'contact', 'lead', 'tags', 'activities']));
    }

    public function update(UpdateDealRequest $request, Deal $deal): DealResource
    {
        $deal->update($request->validated());

        if ($request->has('tags')) {
            $deal->syncTags($request->array('tags'));
        }

        return new DealResource($deal->fresh(['company', 'contact', 'lead', 'tags']));
    }

    public function destroy(Deal $deal): JsonResponse
    {
        $deal->delete();

        return response()->json(null, 204);
    }

    public function advance(Request $request, Deal $deal): DealResource
    {
        $stage = DealStage::from($request->string('stage')->value());
        $updated = $this->crm->advanceDealStage($deal, $stage);

        return new DealResource($updated->load(['company', 'contact', 'lead']));
    }
}
