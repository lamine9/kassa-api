<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('test_items');
    }

    public function down(): void
    {
        Schema::create('test_items', function (Blueprint $table) {
            $table->id();
            $table->ulid('tenant_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->index('tenant_id');
        });
    }
};
