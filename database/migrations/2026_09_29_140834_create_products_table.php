<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('tenant_id');
            $table->ulid('category_id')->nullable();
            $table->ulid('unit_id');

            $table->string('name', 200);
            $table->string('sku', 100)->nullable();
            $table->string('barcode', 100)->nullable();
            $table->text('description')->nullable();

            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);

            $table->decimal('min_stock', 15, 3)->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign(['tenant_id', 'category_id'])
                ->references(['tenant_id', 'id'])
                ->on('categories')
                ->nullOnDelete();

            $table->foreign(['tenant_id', 'unit_id'])
                ->references(['tenant_id', 'id'])
                ->on('units')
                ->restrictOnDelete();

            $table->unique(['tenant_id', 'sku']);
            $table->unique(['tenant_id', 'barcode']);
            $table->unique(['tenant_id', 'id']);

            $table->index('tenant_id');
            $table->index('category_id');
            $table->index('unit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
