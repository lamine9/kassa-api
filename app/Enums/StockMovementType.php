<?php

namespace App\Enums;

enum StockMovementType: string
{
    case OPENING = 'opening';
    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case SALE_RETURN = 'sale_return';
    case PURCHASE_RETURN = 'purchase_return';
    case ADJUSTMENT_IN = 'adjustment_in';
    case ADJUSTMENT_OUT = 'adjustment_out';
    case LOSS = 'loss';

    public function isEntry(): bool
    {
        return match ($this) {
            self::OPENING,
            self::PURCHASE,
            self::SALE_RETURN,
            self::ADJUSTMENT_IN => true,

            self::SALE,
            self::PURCHASE_RETURN,
            self::ADJUSTMENT_OUT,
            self::LOSS => false,
        };
    }

    public function signedQuantity(float $quantity): float
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException(
                'La quantité doit être strictement positive.'
            );
        }

        return $this->isEntry()
            ? $quantity
            : -$quantity;
    }
}
