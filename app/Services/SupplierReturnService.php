<?php

namespace App\Services;

use App\Enums\PurchaseStatus;
use App\Enums\StockMovementType;
use App\Enums\SupplierReturnStatus;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\SupplierReturn;
use App\Models\SupplierReturnItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SupplierReturnService
{
    public function __construct(
        private StockService $stockService
    ) {}

    /**
     * Créer un retour fournisseur en brouillon.
     */
    public function createDraft(
        array $data,
        ?User $user = null
    ): SupplierReturn {
        return DB::transaction(function () use ($data, $user) {

            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($data['purchase_id']);

            if ($purchase->status !== PurchaseStatus::RECEIVED) {
                throw new InvalidArgumentException(
                    "Seuls les achats réceptionnés peuvent faire l'objet d'un retour."
                );
            }

            if (empty($data['items'])) {
                throw new InvalidArgumentException(
                    "Le retour doit contenir au moins un article."
                );
            }

            $return = SupplierReturn::create([
                'tenant_id' => $purchase->tenant_id,
                'purchase_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'reference' => $this->generateReference(),
                'return_date' => $data['return_date'],
                'status' => SupplierReturnStatus::DRAFT,
                'subtotal' => 0,
                'reason' => $data['reason'] ?? null,
                'note' => $data['note'] ?? null,
                'created_by' => $user?->id,
            ]);

            $subtotal = 0;
            $processedItems = [];

            foreach ($data['items'] as $itemData) {
                $purchaseItemId = $itemData['purchase_item_id'];
                $quantity = (float) $itemData['quantity'];

                if ($quantity <= 0) {
                    throw new InvalidArgumentException(
                        "La quantité retournée doit être strictement positive."
                    );
                }

                if (in_array($purchaseItemId, $processedItems, true)) {
                    throw new InvalidArgumentException(
                        "Un même article d'achat ne peut apparaître deux fois dans le retour."
                    );
                }

                $processedItems[] = $purchaseItemId;

                $purchaseItem = PurchaseItem::query()
                    ->where('purchase_id', $purchase->id)
                    ->lockForUpdate()
                    ->findOrFail($purchaseItemId);

                $unitCost = (float) $purchaseItem->unit_cost;
                $total = round($quantity * $unitCost, 2);

                SupplierReturnItem::create([
                    'tenant_id' => $purchase->tenant_id,
                    'supplier_return_id' => $return->id,
                    'purchase_item_id' => $purchaseItem->id,
                    'product_id' => $purchaseItem->product_id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'total' => $total,
                ]);

                $subtotal += $total;
            }

            $return->update([
                'subtotal' => $subtotal,
            ]);

            return $return->fresh(['supplier', 'purchase', 'items.product']);
        });
    }

    public function updateDraft(
        SupplierReturn $supplierReturn,
        array $data
    ): SupplierReturn {
        return DB::transaction(function () use ($supplierReturn, $data) {
            $return = SupplierReturn::query()
                ->lockForUpdate()
                ->findOrFail($supplierReturn->id);

            if ($return->status !== SupplierReturnStatus::DRAFT) {
                throw new InvalidArgumentException(
                    "Seul un retour en brouillon peut être modifié."
                );
            }

            $itemsData = $data['items'] ?? null;

            if ($itemsData !== null && empty($itemsData)) {
                throw new InvalidArgumentException(
                    "Le retour doit contenir au moins un article."
                );
            }

            $return->update([
                'return_date' => $data['return_date'] ?? $return->return_date,
                'reason' => array_key_exists('reason', $data)
                    ? $data['reason']
                    : $return->reason,
                'note' => array_key_exists('note', $data)
                    ? $data['note']
                    : $return->note,
            ]);

            if ($itemsData !== null) {
                $return->items()->delete();

                $subtotal = 0;

                foreach ($itemsData as $itemData) {
                    $purchaseItem = PurchaseItem::query()
                        ->where('purchase_id', $return->purchase_id)
                        ->findOrFail($itemData['purchase_item_id']);

                    $quantity = (float) $itemData['quantity'];
                    $unitCost = (float) $purchaseItem->unit_cost;
                    $total = round($quantity * $unitCost, 2);

                    SupplierReturnItem::create([
                        'tenant_id' => $return->tenant_id,
                        'supplier_return_id' => $return->id,
                        'purchase_item_id' => $purchaseItem->id,
                        'product_id' => $purchaseItem->product_id,
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                        'total' => $total,
                    ]);

                    $subtotal += $total;
                }

                $return->update([
                    'subtotal' => $subtotal,
                ]);
            }

            return $return->fresh([
                'supplier',
                'purchase',
                'items.product',
                'creator',
            ]);
        });
    }

    /**
     * Valider un retour fournisseur et sortir les articles du stock.
     */
    public function validateReturn(
        SupplierReturn $supplierReturn,
        ?User $user = null
    ): SupplierReturn {
        return DB::transaction(function () use ($supplierReturn, $user) {

            $return = SupplierReturn::query()
                ->lockForUpdate()
                ->findOrFail($supplierReturn->id);

            if ($return->status !== SupplierReturnStatus::DRAFT) {
                throw new InvalidArgumentException(
                    "Seul un retour en brouillon peut être validé."
                );
            }

            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($return->purchase_id);

            if ($purchase->status !== PurchaseStatus::RECEIVED) {
                throw new InvalidArgumentException(
                    "L'achat associé n'est plus dans un état permettant le retour."
                );
            }

            $items = $return->items()
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw new InvalidArgumentException(
                    "Impossible de valider un retour sans article."
                );
            }

            foreach ($items as $item) {

                $purchaseItem = PurchaseItem::query()
                    ->where('purchase_id', $purchase->id)
                    ->lockForUpdate()
                    ->findOrFail($item->purchase_item_id);

                /*
                 * Vérifier le cumul des retours déjà validés
                 * pour cette ligne d'achat.
                 */
                $alreadyReturned = SupplierReturnItem::query()
                    ->where('purchase_item_id', $purchaseItem->id)
                    ->whereHas('supplierReturn', function ($query) {
                        $query->where(
                            'status',
                            SupplierReturnStatus::VALIDATED->value
                        );
                    })
                    ->sum('quantity');

                $remainingQuantity =
                    (float) $purchaseItem->quantity - (float) $alreadyReturned;

                if ((float) $item->quantity > $remainingQuantity) {
                    throw new InvalidArgumentException(
                        "La quantité à retourner dépasse la quantité encore retournable pour le produit {$item->product_id}."
                    );
                }

                /*
                 * Verrouiller le produit avant de vérifier
                 * le stock physique disponible.
                 */
                $product = Product::query()
                    ->lockForUpdate()
                    ->findOrFail($item->product_id);

                $currentStock = $this->stockService->currentStock($product);

                if ((float) $item->quantity > $currentStock) {
                    throw new InvalidArgumentException(
                        "Stock insuffisant pour retourner le produit {$product->name}. Stock disponible : {$currentStock}."
                    );
                }

                /*
                 * Enregistrer la sortie de stock.
                 */
                $this->stockService->remove(
                    product: $product,
                    type: StockMovementType::PURCHASE_RETURN,
                    quantity: (float) $item->quantity,
                    unitCost: (float) $item->unit_cost,
                    note: "Retour fournisseur {$return->reference}",
                    user: $user,
                    referenceType: SupplierReturn::class,
                    referenceId: $return->id,
                );
            }

            $return->update([
                'status' => SupplierReturnStatus::VALIDATED,
            ]);

            return $return->fresh([
                'supplier',
                'purchase',
                'items.product',
            ]);
        });
    }

    /**
     * Annuler un retour encore en brouillon.
     */
    public function cancelDraft(
        SupplierReturn $supplierReturn
    ): SupplierReturn {
        return DB::transaction(function () use ($supplierReturn) {

            $return = SupplierReturn::query()
                ->lockForUpdate()
                ->findOrFail($supplierReturn->id);

            if ($return->status !== SupplierReturnStatus::DRAFT) {
                throw new InvalidArgumentException(
                    "Seul un retour en brouillon peut être annulé."
                );
            }

            $return->update([
                'status' => SupplierReturnStatus::CANCELLED,
            ]);

            return $return->fresh([
                'supplier',
                'purchase',
                'items.product',
            ]);
        });
    }

    /**
     * Générer une référence unique par tenant.
     */
    private function generateReference(): string
    {
        do {
            $reference = 'RET-' . now()->format('Ymd')
                . '-' . Str::upper(Str::random(6));

        } while (
            SupplierReturn::query()
                ->where('reference', $reference)
                ->exists()
        );

        return $reference;
    }
}
