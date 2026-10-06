<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = QueryBuilder::for(Product::query())
            ->allowedFilters(
                'name',
                'sku',
                'barcode',
                'category_id',
                'unit_id',
                'is_active',
            )
            ->allowedSorts(
                'name',
                'purchase_price',
                'selling_price',
                'created_at',
            )
            ->paginate(
                perPage: min((int) $request->integer('per_page', 15), 100)
            )
            ->appends($request->query());

        return response()->json([
            'message' => 'Liste des produits.',
            'data' => $products,
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json([
            'message' => 'Produit créé avec succès.',
            'data' => $product->load(['category', 'unit']),
        ], 201);
    }

    public function show(string $product): JsonResponse
    {
        $product = Product::query()
            ->with(['category', 'unit'])
            ->findOrFail($product);

        return response()->json([
            'message' => 'Détail du produit.',
            'data' => $product,
        ]);
    }

    public function update(
        UpdateProductRequest $request,
        string $product
    ): JsonResponse {
        $product = Product::query()->findOrFail($product);

        $product->update($request->validated());

        return response()->json([
            'message' => 'Produit mis à jour avec succès.',
            'data' => $product->fresh(['category', 'unit']),
        ]);
    }

    public function destroy(string $product): JsonResponse
    {
        $product = Product::query()->findOrFail($product);

        try {
            $product->delete();
        } catch (QueryException $e) {
            if ($e->getCode() === '23503') {
                return response()->json([
                    'message' => 'Impossible de supprimer ce produit car il est utilisé dans un ou plusieurs mouvements de stock.',
                ], 409);
            }

            throw $e;
        }

        return response()->json([
            'message' => 'Produit supprimé avec succès.',
        ]);
    }

    public function stock(
        string $product,
        StockService $stockService
    ): JsonResponse {
        $product = Product::query()->findOrFail($product);

        $stock = $stockService->currentStock($product);

        return response()->json([
            'message' => 'Stock actuel du produit.',
            'data' => [
                'product_id' => $product->id,
                'product' => $product->name,
                'stock' => $stock,
                'min_stock' => (float) $product->min_stock,
                'is_low_stock' => $stock <= (float) $product->min_stock,
            ],
        ]);
    }
}
