<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tenant_id');
            $table->ulid('supplier_id');

            $table->string('reference', 50);
            $table->string('supplier_reference', 100)->nullable();

            $table->date('purchase_date');

            $table->string('status', 30)->default('draft');

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            $table->text('note')->nullable();

            $table->foreignId('created_by')
                ->nullable();

            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign(['tenant_id', 'supplier_id'])
                ->references(['tenant_id', 'id'])
                ->on('suppliers')
                ->restrictOnDelete();

            $table->foreign(['tenant_id', 'created_by'])
                ->references(['tenant_id', 'id'])
                ->on('users')
                ->nullOnDelete();

            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'reference']);

            $table->index('tenant_id');
            $table->index(['tenant_id', 'supplier_id']);
            $table->index(['tenant_id', 'purchase_date']);
            $table->index(['tenant_id', 'status']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
