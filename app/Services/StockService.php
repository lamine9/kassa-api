<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    public function add(
        Product $product,
        StockMovementType $type,
        float $quantity,
        ?float $unitCost = null,
        ?string $note = null,
        ?User $user = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
    ): StockMovement {
        if (!$type->isEntry()) {
            throw new InvalidArgumentException(
                "Le type {$type->value} n'est pas une entrée de stock."
            );
        }

        return $this->createMovement(
            product: $product,
            type: $type,
            quantity: $quantity,
            unitCost: $unitCost,
            note: $note,
            user: $user,
            referenceType: $referenceType,
            referenceId: $referenceId,
        );
    }

    public function remove(
        Product $product,
        StockMovementType $type,
        float $quantity,
        ?float $unitCost = null,
        ?string $note = null,
        ?User $user = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
    ): StockMovement {
        if ($type->isEntry()) {
            throw new InvalidArgumentException(
                "Le type {$type->value} n'est pas une sortie de stock."
            );
        }

        return $this->createMovement(
            product: $product,
            type: $type,
            quantity: $quantity,
            unitCost: $unitCost,
            note: $note,
            user: $user,
            referenceType: $referenceType,
            referenceId: $referenceId,
        );
    }

    public function currentStock(Product $product): float
    {
        return (float) StockMovement::query()
            ->where('product_id', $product->id)
            ->sum('quantity');
    }

    private function createMovement(
        Product $product,
        StockMovementType $type,
        float $quantity,
        ?float $unitCost,
        ?string $note,
        ?User $user,
        ?string $referenceType,
        ?string $referenceId,
    ): StockMovement {
        $signedQuantity = $type->signedQuantity($quantity);

        return DB::transaction(function () use (
            $product,
            $type,
            $signedQuantity,
            $unitCost,
            $note,
            $user,
            $referenceType,
            $referenceId,
        ) {
            return StockMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $signedQuantity,
                'unit_cost' => $unitCost,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $note,
                'created_by' => $user?->id,
            ]);
        });
    }
}
