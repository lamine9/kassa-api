<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'customers.view',
            'customers.create',
            'customers.update',
            'customers.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }

    public function down(): void
    {
        Permission::whereIn('name', [
            'customers.view',
            'customers.create',
            'customers.update',
            'customers.delete',
        ])->delete();
    }
};
