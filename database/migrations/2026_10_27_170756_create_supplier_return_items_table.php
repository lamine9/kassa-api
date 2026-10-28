<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_return_items', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('tenant_id');
            $table->ulid('supplier_return_id');
            $table->ulid('purchase_item_id');
            $table->ulid('product_id');

            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('total', 15, 2);

            $table->timestamps();

            // Relations avec isolation par tenant
            $table->foreign(
                ['tenant_id', 'supplier_return_id']
            )->references(
                ['tenant_id', 'id']
            )->on('supplier_returns')->cascadeOnDelete();

            $table->foreign(
                ['tenant_id', 'purchase_item_id']
            )->references(
                ['tenant_id', 'id']
            )->on('purchase_items')->restrictOnDelete();

            $table->foreign(
                ['tenant_id', 'product_id']
            )->references(
                ['tenant_id', 'id']
            )->on('products')->restrictOnDelete();

            // Éviter deux lignes identiques pour le même achat
            $table->unique(
                ['supplier_return_id', 'purchase_item_id'],
                'supplier_return_items_return_purchase_item_unique'
            );

            $table->index(
                ['tenant_id', 'purchase_item_id'],
                'supplier_return_items_tenant_purchase_item_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_return_items');
    }
};
