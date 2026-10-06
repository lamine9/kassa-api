<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockMovementRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class StockMovementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $movements = QueryBuilder::for(
            StockMovement::query()->with(['product', 'creator'])
        )
            ->allowedFilters(
                'product_id',
                'type',
                'reference_type',
                'created_by',
            )
            ->allowedSorts(
                'created_at',
                'quantity',
                'unit_cost',
            )
            ->defaultSort('-created_at')
            ->paginate(
                perPage: min((int) $request->integer('per_page', 15), 100)
            )
            ->appends($request->query());

        return response()->json([
            'message' => 'Liste des mouvements de stock.',
            'data' => $movements,
        ]);
    }

    public function store(
        StoreStockMovementRequest $request,
        StockService $stockService
    ): JsonResponse {
        $data = $request->validated();

        $product = Product::query()->findOrFail($data['product_id']);

        $type = StockMovementType::from($data['type']);

        if ($type->isEntry()) {
            $movement = $stockService->add(
                product: $product,
                type: $type,
                quantity: (float) $data['quantity'],
                unitCost: isset($data['unit_cost'])
                    ? (float) $data['unit_cost']
                    : null,
                note: $data['note'] ?? null,
                user: $request->user(),
                referenceType: $data['reference_type'] ?? null,
                referenceId: $data['reference_id'] ?? null,
            );
        } else {
            $movement = $stockService->remove(
                product: $product,
                type: $type,
                quantity: (float) $data['quantity'],
                unitCost: isset($data['unit_cost'])
                    ? (float) $data['unit_cost']
                    : null,
                note: $data['note'] ?? null,
                user: $request->user(),
                referenceType: $data['reference_type'] ?? null,
                referenceId: $data['reference_id'] ?? null,
            );
        }

        return response()->json([
            'message' => 'Mouvement de stock enregistré avec succès.',
            'data' => $movement->load(['product', 'creator']),
        ], 201);
    }

}
