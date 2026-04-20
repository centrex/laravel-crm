<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Controllers\Api;

use Centrex\Crm\Http\Requests\{StoreContactRequest, UpdateContactRequest};
use Centrex\Crm\Http\Resources\ContactResource;
use Centrex\Crm\Models\Contact;
use Illuminate\Http\{JsonResponse, Request, Resources\Json\AnonymousResourceCollection};
use Illuminate\Routing\Controller;

class ContactController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Contact::query()->with(['company', 'tags', 'latestClvSnapshot']);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();
            $query->where(static function ($q) use ($search): void {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->integer('company_id'));
        }

        if ($request->has('is_primary')) {
            $query->where('is_primary', $request->boolean('is_primary'));
        }

        return ContactResource::collection(
            $query->orderBy('first_name')->paginate($request->integer('per_page', 15)),
        );
    }

    public function store(StoreContactRequest $request): ContactResource
    {
        $contact = Contact::query()->create($request->validated());

        if ($request->filled('tags')) {
            $contact->syncTags($request->array('tags'));
        }

        return new ContactResource($contact->load(['company', 'tags', 'latestClvSnapshot']));
    }

    public function show(Contact $contact): ContactResource
    {
        return new ContactResource($contact->load(['company', 'tags', 'deals', 'leads', 'clvSnapshots']));
    }

    public function update(UpdateContactRequest $request, Contact $contact): ContactResource
    {
        $contact->update($request->validated());

        if ($request->has('tags')) {
            $contact->syncTags($request->array('tags'));
        }

        return new ContactResource($contact->fresh(['company', 'tags', 'latestClvSnapshot']));
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json(null, 204);
    }
}
