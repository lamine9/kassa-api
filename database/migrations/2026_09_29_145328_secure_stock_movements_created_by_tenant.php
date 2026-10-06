<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign(['tenant_id', 'created_by'])
                ->references(['tenant_id', 'id'])
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['tenant_id', 'created_by']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }
};
