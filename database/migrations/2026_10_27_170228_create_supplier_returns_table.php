<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_returns', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('tenant_id');
            $table->ulid('purchase_id');
            $table->ulid('supplier_id');

            $table->string('reference', 50);
            $table->date('return_date');

            $table->string('status', 30)->default('draft');

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->text('reason')->nullable();
            $table->text('note')->nullable();

            $table->foreignId('created_by')->nullable();

            $table->timestamps();

            // Unicité de la référence par entreprise
            $table->unique(
                ['tenant_id', 'reference'],
                'supplier_returns_tenant_reference_unique'
            );
            $table->unique(
                ['tenant_id', 'id'],
                'supplier_returns_tenant_id_id_unique'
            );

            // Relations avec isolation par tenant
            $table->foreign(
                ['tenant_id', 'purchase_id']
            )->references(
                ['tenant_id', 'id']
            )->on('purchases')->restrictOnDelete();

            $table->foreign(
                ['tenant_id', 'supplier_id']
            )->references(
                ['tenant_id', 'id']
            )->on('suppliers')->restrictOnDelete();

            $table->foreign(
                ['tenant_id', 'created_by']
            )->references(
                ['tenant_id', 'id']
            )->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_returns');
    }
};
