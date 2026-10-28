<?php

namespace App\Services;

use App\Enums\PurchaseStatus;
use App\Enums\StockMovementType;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseService
{
    public function receive(
        Purchase $purchase,
        ?User $user = null
    ): Purchase {
        return DB::transaction(function () use ($purchase, $user) {
            $purchase = Purchase::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($purchase->id);

            if (!$purchase->status->canReceive()) {
                throw new InvalidArgumentException(
                    "L'achat {$purchase->reference} ne peut pas être réceptionné."
                );
            }

            if ($purchase->items->isEmpty()) {
                throw new InvalidArgumentException(
                    "Impossible de réceptionner un achat sans lignes."
                );
            }

            foreach ($purchase->items as $item) {
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'type' => StockMovementType::PURCHASE,
                    'quantity' => (float) $item->quantity,
                    'unit_cost' => (float) $item->unit_cost,
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'created_by' => $user?->id,
                    'note' => "Réception de l'achat {$purchase->reference}",
                ]);
            }

            $purchase->update([
                'status' => PurchaseStatus::RECEIVED,
            ]);

            return $purchase->fresh(['supplier', 'items.product']);
        });
    }

    public function delete(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($purchase->id);

            if ($purchase->status !== PurchaseStatus::DRAFT) {
                throw new InvalidArgumentException(
                    "L'achat {$purchase->reference} ne peut pas être supprimé."
                );
            }

            $purchase->delete();
        });
    }
}
