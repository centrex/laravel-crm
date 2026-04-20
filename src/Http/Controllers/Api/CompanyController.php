<?php

declare(strict_types = 1);

namespace Centrex\Crm\Http\Controllers\Api;

use Centrex\Crm\Http\Requests\{StoreCompanyRequest, UpdateCompanyRequest};
use Centrex\Crm\Http\Resources\CompanyResource;
use Centrex\Crm\Models\Company;
use Illuminate\Http\{JsonResponse, Request, Resources\Json\AnonymousResourceCollection};
use Illuminate\Routing\Controller;

class CompanyController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Company::query()->with(['contacts', 'tags']);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();
            $query->where(static function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('industry')) {
            $query->where('industry', $request->string('industry')->value());
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return CompanyResource::collection(
            $query->orderBy('name')->paginate($request->integer('per_page', 15)),
        );
    }

    public function store(StoreCompanyRequest $request): CompanyResource
    {
        $company = Company::query()->create($request->validated());

        if ($request->filled('tags')) {
            $company->syncTags($request->array('tags'));
        }

        return new CompanyResource($company->load(['contacts', 'tags']));
    }

    public function show(Company $company): CompanyResource
    {
        return new CompanyResource($company->load(['contacts', 'tags', 'leads', 'deals']));
    }

    public function update(UpdateCompanyRequest $request, Company $company): CompanyResource
    {
        $company->update($request->validated());

        if ($request->has('tags')) {
            $company->syncTags($request->array('tags'));
        }

        return new CompanyResource($company->fresh(['contacts', 'tags']));
    }

    public function destroy(Company $company): JsonResponse
    {
        $company->delete();

        return response()->json(null, 204);
    }
}
