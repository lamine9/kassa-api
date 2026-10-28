<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpParser\Node\Scalar\String_;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Requests\ReceivePurchaseRequest;
use App\Services\PurchaseService;

class PurchaseController extends Controller
{
    /**
     * Liste les achats de l'entreprise connectée.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $purchases = QueryBuilder::for(
            Purchase::query()->with([
                'supplier',
                'items.product',
                'creator',
            ])
        )
            ->allowedFilters(
                AllowedFilter::exact('status'),
                AllowedFilter::exact('supplier_id'),
                AllowedFilter::partial('reference')
            )
            ->allowedSorts(
                'purchase_date',
                'reference',
                'total',
                'created_at',
            )
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->appends($request->query());

        return response()->json([
            'message' => 'Liste des achats récupérée avec succès.',
            'data' => $purchases,
        ]);
    }

    public function show(String $purchase): JsonResponse
    {
        $purchase = Purchase::query()
            ->findOrFail($purchase);
        $purchase->load([
            'supplier',
            'items.product',
            'creator',
        ]);

        return response()->json([
            'message' => 'Détail de l’achat récupéré avec succès.',
            'data' => $purchase,
        ]);
    }

    public function update(
        UpdatePurchaseRequest $request,
        Purchase $purchase
    ): JsonResponse {
        $data = $request->validated();

        $purchase = DB::transaction(function () use ($data, $purchase) {
            $purchase = Purchase::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($purchase->id);

            if ($purchase->status !== PurchaseStatus::DRAFT) {
                abort(422, 'Seuls les achats en brouillon peuvent être modifiés.');
            }

            $purchase->fill([
                'supplier_id' => $data['supplier_id'] ?? $purchase->supplier_id,
                'supplier_reference' => array_key_exists('supplier_reference', $data)
                    ? $data['supplier_reference']
                    : $purchase->supplier_reference,
                'purchase_date' => $data['purchase_date'] ?? $purchase->purchase_date,
                'note' => array_key_exists('note', $data)
                    ? $data['note']
                    : $purchase->note,
            ]);

            if (isset($data['items'])) {
                $subtotal = 0.0;
                $lineDiscountTotal = 0.0;
                $lineTaxTotal = 0.0;
                $items = [];

                foreach ($data['items'] as $item) {
                    $quantity = (float) $item['quantity'];
                    $unitCost = (float) $item['unit_cost'];
                    $lineDiscount = (float) ($item['discount'] ?? 0);
                    $lineTax = (float) ($item['tax'] ?? 0);

                    $lineSubtotal = round($quantity * $unitCost, 2);

                    if ($lineDiscount > $lineSubtotal) {
                        abort(
                            422,
                            'La remise d’une ligne ne peut pas dépasser son montant brut.'
                        );
                    }

                    $subtotal += $lineSubtotal;
                    $lineDiscountTotal += $lineDiscount;
                    $lineTaxTotal += $lineTax;

                    $items[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                        'discount' => $lineDiscount,
                        'tax' => $lineTax,
                        'total' => round(
                            $lineSubtotal - $lineDiscount + $lineTax,
                            2
                        ),
                    ];
                }

                $purchase->subtotal = round($subtotal, 2);

                $purchase->items()->delete();

                foreach ($items as $item) {
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        ...$item,
                    ]);
                }

                $purchase->discount = round(
                    $lineDiscountTotal + (float) ($data['discount'] ?? 0),
                    2
                );

                $purchase->tax = round(
                    $lineTaxTotal + (float) ($data['tax'] ?? 0),
                    2
                );
            } else {
                if (array_key_exists('discount', $data)) {
                    $purchase->discount = (float) $data['discount'];
                }

                if (array_key_exists('tax', $data)) {
                    $purchase->tax = (float) $data['tax'];
                }
            }

            if ($purchase->discount > $purchase->subtotal) {
                abort(422, 'La remise ne peut pas dépasser le sous-total.');
            }

            $purchase->total = round(
                (float) $purchase->subtotal
                - (float) $purchase->discount
                + (float) $purchase->tax,
                2
            );

            if ($purchase->total < 0) {
                abort(422, 'Le total de l’achat ne peut pas être négatif.');
            }

            $purchase->save();

            return $purchase->fresh([
                'supplier',
                'items.product',
                'creator',
            ]);
        });

        return response()->json([
            'message' => 'Achat modifié avec succès.',
            'data' => $purchase,
        ]);
    }

    /**
     * Crée un achat en brouillon avec ses lignes.
     * Aucun mouvement de stock n'est créé à cette étape.
     */
    public function store(StorePurchaseRequest $request): JsonResponse
    {
        $data = $request->validated();

        $purchase = DB::transaction(function () use ($data, $request) {
            $subtotal = 0.0;
            $lineDiscountTotal = 0.0;
            $lineTaxTotal = 0.0;
            $items = [];

            foreach ($data['items'] as $item) {
                $quantity = (float) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];
                $lineDiscount = (float) ($item['discount'] ?? 0);
                $lineTax = (float) ($item['tax'] ?? 0);

                $lineSubtotal = round($quantity * $unitCost, 2);

                if ($lineDiscount > $lineSubtotal) {
                    abort(
                        422,
                        'La remise d’une ligne ne peut pas dépasser son montant brut.'
                    );
                }

                $lineTotal = round(
                    $lineSubtotal - $lineDiscount + $lineTax,
                    2
                );

                $subtotal += $lineSubtotal;
                $lineDiscountTotal += $lineDiscount;
                $lineTaxTotal += $lineTax;

                $items[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'discount' => $lineDiscount,
                    'tax' => $lineTax,
                    'total' => $lineTotal,
                ];
            }

            $subtotal = round($subtotal, 2);

            $globalDiscount = (float) ($data['discount'] ?? 0);
            $globalTax = (float) ($data['tax'] ?? 0);

            if ($globalDiscount > $subtotal) {
                abort(
                    422,
                    'La remise globale ne peut pas dépasser le sous-total.'
                );
            }

            $totalDiscount = round(
                $lineDiscountTotal + $globalDiscount,
                2
            );

            $totalTax = round(
                $lineTaxTotal + $globalTax,
                2
            );

            $total = round(
                $subtotal - $totalDiscount + $totalTax,
                2
            );

            if ($total < 0) {
                abort(422, 'Le total de l’achat ne peut pas être négatif.');
            }

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'reference' => 'ACH-' . strtoupper(Str::random(10)),
                'supplier_reference' => $data['supplier_reference'] ?? null,
                'purchase_date' => $data['purchase_date'],
                'status' => PurchaseStatus::DRAFT,
                'subtotal' => $subtotal,
                'discount' => $totalDiscount,
                'tax' => $totalTax,
                'total' => $total,
                'note' => $data['note'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($items as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    ...$item,
                ]);
            }

            return $purchase->load([
                'supplier',
                'items.product',
                'creator',
            ]);
        });

        return response()->json([
            'message' => 'Achat créé avec succès.',
            'data' => $purchase,
        ], 201);
    }

    public function destroy(Purchase $purchase): JsonResponse
    {
        DB::transaction(function () use ($purchase) {
            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($purchase->id);

            if ($purchase->status !== PurchaseStatus::DRAFT) {
                abort(
                    422,
                    'Seuls les achats en brouillon peuvent être supprimés.'
                );
            }

            $purchase->delete();
        });

        return response()->json([
            'message' => 'Achat en brouillon supprimé avec succès.',
        ]);
    }

    public function receive(
        ReceivePurchaseRequest $request,
        Purchase $purchase,
        PurchaseService $purchaseService
    ): JsonResponse {
        try {
            $purchase = $purchaseService->receive(
                $purchase,
                $request->user()
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Achat réceptionné avec succès.',
            'data' => $purchase,
        ]);
    }
}
