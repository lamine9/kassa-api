<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('tenant_id');

            $table->string('name', 100);
            $table->string('symbol', 20);

            $table->unsignedTinyInteger('decimal_places')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->unique(['tenant_id', 'name']);
            $table->unique(['tenant_id', 'symbol']);
            $table->unique(['tenant_id', 'id']);

            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
