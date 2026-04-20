<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Controllers\Api;

use Centrex\Crm\Http\Requests\{StoreActivityRequest, UpdateActivityRequest};
use Centrex\Crm\Http\Resources\ActivityResource;
use Centrex\Crm\Models\Activity;
use Illuminate\Http\{JsonResponse, Request, Resources\Json\AnonymousResourceCollection};
use Illuminate\Routing\Controller;

class ActivityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Activity::query()->with('subject');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->value());
        }

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->integer('owner_id'));
        }

        if ($request->boolean('pending')) {
            $query->whereNull('completed_at');
        }

        if ($request->boolean('overdue')) {
            $query->whereNull('completed_at')->where('due_at', '<', now());
        }

        return ActivityResource::collection(
            $query->orderBy('due_at')->paginate($request->integer('per_page', 15)),
        );
    }

    public function store(StoreActivityRequest $request): ActivityResource
    {
        $activity = Activity::query()->create($request->validated());

        return new ActivityResource($activity->load('subject'));
    }

    public function show(Activity $activity): ActivityResource
    {
        return new ActivityResource($activity->load('subject'));
    }

    public function update(UpdateActivityRequest $request, Activity $activity): ActivityResource
    {
        $activity->update($request->validated());

        return new ActivityResource($activity->fresh('subject'));
    }

    public function destroy(Activity $activity): JsonResponse
    {
        $activity->delete();

        return response()->json(null, 204);
    }

    public function complete(Activity $activity): ActivityResource
    {
        $activity->update(['completed_at' => now()]);

        return new ActivityResource($activity->fresh('subject'));
    }
}
