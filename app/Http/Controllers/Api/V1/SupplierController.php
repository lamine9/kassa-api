<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class SupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $suppliers = QueryBuilder::for(Supplier::query())
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
            'message' => 'Liste des fournisseurs.',
            'data' => $suppliers,
        ]);
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::create($request->validated());

        return response()->json([
            'message' => 'Fournisseur créé avec succès.',
            'data' => $supplier,
        ], 201);
    }

    public function show(string $supplier): JsonResponse
    {
        $supplier = Supplier::query()
            ->findOrFail($supplier);

        return response()->json([
            'message' => 'Détail du fournisseur.',
            'data' => $supplier,
        ]);
    }

    public function update(
        UpdateSupplierRequest $request,
        string $supplier
    ): JsonResponse {
        $supplier = Supplier::query()
            ->findOrFail($supplier);

        $supplier->update($request->validated());

        return response()->json([
            'message' => 'Fournisseur mis à jour avec succès.',
            'data' => $supplier->fresh(),
        ]);
    }

    public function destroy(string $supplier): JsonResponse
    {
        $supplier = Supplier::query()
            ->findOrFail($supplier);

        $supplier->delete();

        return response()->json([
            'message' => 'Fournisseur supprimé avec succès.',
        ]);
    }
}
