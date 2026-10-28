<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('tenant_id');
            $table->ulid('purchase_id');
            $table->ulid('product_id');

            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_cost', 15, 2);

            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2);

            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign(['tenant_id', 'purchase_id'])
                ->references(['tenant_id', 'id'])
                ->on('purchases')
                ->cascadeOnDelete();

            $table->foreign(['tenant_id', 'product_id'])
                ->references(['tenant_id', 'id'])
                ->on('products')
                ->restrictOnDelete();

            $table->unique(['tenant_id', 'id']);

            $table->index(['tenant_id', 'purchase_id']);
            $table->index(['tenant_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
