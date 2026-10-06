<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $customers = QueryBuilder::for(Customer::query())
            ->allowedFilters(
                'name',
                'phone',
                'email',
                'is_active',
            )
            ->allowedSorts(
                'name',
                'created_at',
                'credit_limit',
            )
            ->defaultSort('name')
            ->paginate(
                perPage: min(
                    (int) $request->integer('per_page', 15),
                    100
                )
            )
            ->appends($request->query());

        return response()->json([
            'message' => 'Liste des clients.',
            'data' => $customers,
        ]);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->validated());

        return response()->json([
            'message' => 'Client créé avec succès.',
            'data' => $customer,
        ], 201);
    }

    public function show(string $customer): JsonResponse
    {
        $customer = Customer::query()
            ->findOrFail($customer);

        return response()->json([
            'message' => 'Détail du client.',
            'data' => $customer,
        ]);
    }

    public function update(
        UpdateCustomerRequest $request,
        string $customer
    ): JsonResponse {
        $customer = Customer::query()
            ->findOrFail($customer);

        $customer->update($request->validated());

        return response()->json([
            'message' => 'Client mis à jour avec succès.',
            'data' => $customer->fresh(),
        ]);
    }

    public function destroy(string $customer): JsonResponse
    {
        $customer = Customer::query()
            ->findOrFail($customer);

        $customer->delete();

        return response()->json([
            'message' => 'Client supprimé avec succès.',
        ]);
    }
}
