<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Controllers\Api;

use Centrex\Crm\Crm;
use Centrex\Crm\Http\Resources\ClvSnapshotResource;
use Centrex\Crm\Models\Contact;
use Illuminate\Http\{JsonResponse, Request, Resources\Json\AnonymousResourceCollection};
use Illuminate\Routing\Controller;

class ClvController extends Controller
{
    public function __construct(private readonly Crm $crm) {}

    public function show(Contact $contact): ClvSnapshotResource|JsonResponse
    {
        $snapshot = $contact->latestClvSnapshot;

        if ($snapshot === null) {
            return response()->json(['message' => 'No CLV snapshot found. Run crm:calculate-clv to generate.'], 404);
        }

        return new ClvSnapshotResource($snapshot);
    }

    public function recalculate(Request $request, Contact $contact): ClvSnapshotResource
    {
        $horizonMonths = $request->integer('horizon_months', (int) config('crm.clv_horizon_months', 12));
        $snapshot = $this->crm->calculateClv($contact, $horizonMonths);

        return new ClvSnapshotResource($snapshot);
    }

    public function leaderboard(Request $request): AnonymousResourceCollection
    {
        $snapshots = $this->crm->getClvLeaderboard(
            $request->integer('limit', 10),
            $request->integer('horizon_months', (int) config('crm.clv_horizon_months', 12)),
        );

        return ClvSnapshotResource::collection($snapshots);
    }

    public function batchRecalculate(Request $request): JsonResponse
    {
        $horizonMonths = $request->integer('horizon_months', (int) config('crm.clv_horizon_months', 12));
        $count = $this->crm->recalculateAllClv($horizonMonths);

        return response()->json(['message' => "CLV recalculated for {$count} contacts.", 'count' => $count]);
    }
}
