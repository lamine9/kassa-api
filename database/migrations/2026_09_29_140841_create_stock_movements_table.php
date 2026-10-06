<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('tenant_id');
            $table->ulid('product_id');

            $table->string('type', 50);

            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_cost', 15, 2)->nullable();

            $table->string('reference_type', 100)->nullable();
            $table->ulid('reference_id')->nullable();

            $table->text('note')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign(['tenant_id', 'product_id'])
                ->references(['tenant_id', 'id'])
                ->on('products')
                ->restrictOnDelete();

            $table->index('tenant_id');
            $table->index('product_id');
            $table->index('type');
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_by');

            $table->unique(['tenant_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
