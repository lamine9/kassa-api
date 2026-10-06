<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use Illuminate\Database\QueryException;

class UnitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $units = \Spatie\QueryBuilder\QueryBuilder::for(
            \App\Models\Unit::query()
        )
            ->allowedFilters(
                'name',
                'symbol',
                'is_active',
            )
            ->allowedSorts(
                'name',
                'symbol',
                'created_at',
            )
            ->paginate(
                perPage: min((int) $request->integer('per_page', 15), 100)
            )
            ->appends($request->query());

        return response()->json([
            'message' => 'Liste des unités.',
            'data' => $units,
        ]);
    }

    public function store(StoreUnitRequest $request): JsonResponse
    {
        $unit = \App\Models\Unit::create($request->validated());

        return response()->json([
            'message' => 'Unité créée avec succès.',
            'data' => $unit,
        ], 201);
    }

    public function show(string $unit): JsonResponse
    {
        $unit = \App\Models\Unit::query()->findOrFail($unit);

        return response()->json([
            'message' => 'Détail de l’unité.',
            'data' => $unit,
        ]);
    }

    public function update(UpdateUnitRequest $request, string $unit): JsonResponse
    {
        $unit = \App\Models\Unit::query()->findOrFail($unit);

        $unit->update($request->validated());

        return response()->json([
            'message' => 'Unité mise à jour avec succès.',
            'data' => $unit->fresh(),
        ]);
    }

    public function destroy(string $unit): JsonResponse
    {
        $unit = \App\Models\Unit::query()->findOrFail($unit);

        try {
            $unit->delete();
        } catch (QueryException $e) {
            if ($e->getCode() === '23001') {
                return response()->json([
                    'message' => 'Impossible de supprimer cette unité car elle est utilisée par un ou plusieurs produits.',
                ], 409);
            }

            throw $e;
        }

        return response()->json([
            'message' => 'Unité supprimée avec succès.',
        ]);
    }
}
