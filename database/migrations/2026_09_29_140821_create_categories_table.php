<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('tenant_id');

            $table->string('name', 150);
            $table->string('slug', 180);
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->unique(['tenant_id', 'name']);
            $table->unique(['tenant_id', 'slug']);
            $table->unique(['tenant_id', 'id']);

            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
