<?php

namespace App\Models;

use App\Enums\StockMovementType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class StockMovement extends Model
{
    use HasFactory;
    use HasUlids;
    use BelongsToTenant;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'unit_cost',
        'reference_type',
        'reference_id',
        'note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => StockMovementType::class,
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (StockMovement $movement): void {
            if (!$movement->type instanceof StockMovementType) {
                throw new InvalidArgumentException(
                    'Le type de mouvement de stock est invalide.'
                );
            }

            if ((float) $movement->quantity === 0.0) {
                throw new InvalidArgumentException(
                    'La quantité du mouvement doit être différente de zéro.'
                );
            }

            $isEntry = $movement->type->isEntry();
            $isPositive = (float) $movement->quantity > 0;

            if ($isEntry !== $isPositive) {
                throw new InvalidArgumentException(
                    "La quantité {$movement->quantity} est incompatible avec le type {$movement->type->value}."
                );
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
