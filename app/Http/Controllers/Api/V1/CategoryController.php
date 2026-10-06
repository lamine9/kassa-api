<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = \Spatie\QueryBuilder\QueryBuilder::for(
            Category::query()
        )
            ->allowedFilters(
                'name',
                'slug',
                'is_active',
            )
            ->allowedSorts(
                'name',
                'slug',
                'created_at',
            )
            ->paginate(
                perPage: min((int) $request->integer('per_page', 15), 100)
            )
            ->appends($request->query());

        return response()->json([
            'message' => 'Liste des catégories.',
            'data' => $categories,
        ]);
    }
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return response()->json([
            'message' => 'Catégorie créée avec succès.',
            'data' => $category,
        ], 201);
    }

    public function show(string $category): JsonResponse
    {
        $category = Category::query()->findOrFail($category);

        return response()->json([
            'message' => 'Détail de la catégorie.',
            'data' => $category,
        ]);
    }

    public function update(
        UpdateCategoryRequest $request,
        string $category
    ): JsonResponse {
        $category = Category::query()->findOrFail($category);

        $category->update($request->validated());

        return response()->json([
            'message' => 'Catégorie mise à jour avec succès.',
            'data' => $category->fresh(),
        ]);
    }

    public function destroy(string $category): JsonResponse
    {
        $category = Category::query()->findOrFail($category);

        $category->delete();

        return response()->json([
            'message' => 'Catégorie supprimée avec succès.',
        ]);
    }

}
