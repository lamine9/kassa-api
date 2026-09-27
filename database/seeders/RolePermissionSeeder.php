<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Produits
            'products.view',
            'products.create',
            'products.update',
            'products.delete',

            // Catégories
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',

            // Stock
            'stock.view',
            'stock.create',
            'stock.adjust',
            'stock.transfer',

            // Ventes
            'sales.view',
            'sales.create',
            'sales.update',
            'sales.cancel',

            // Paiements
            'payments.view',
            'payments.create',
            'payments.refund',

            // Clients
            'customers.view',
            'customers.create',
            'customers.update',
            'customers.delete',

            // Fournisseurs
            'suppliers.view',
            'suppliers.create',
            'suppliers.update',
            'suppliers.delete',

            // Caisse
            'cash_registers.view',
            'cash_registers.manage',
            'cash_sessions.open',
            'cash_sessions.close',

            // Employés
            'employees.view',
            'employees.create',
            'employees.update',
            'employees.delete',

            // Dépenses
            'expenses.view',
            'expenses.create',
            'expenses.update',
            'expenses.delete',

            // Rapports
            'reports.view',

            // Utilisateurs
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // Paramètres
            'settings.view',
            'settings.update',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $roles = [
            'owner',
            'admin',
            'manager',
            'cashier',
            'stock_manager',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        Role::findByName('owner')->syncPermissions(
            Permission::all()
        );

        Role::findByName('admin')->syncPermissions(
            Permission::whereNotIn('name', [
                'users.delete',
            ])->get()
        );

        Role::findByName('manager')->syncPermissions([
            'products.view',
            'products.create',
            'products.update',
            'categories.view',
            'categories.create',
            'categories.update',

            'stock.view',
            'stock.create',
            'stock.adjust',
            'stock.transfer',

            'sales.view',
            'sales.create',
            'sales.update',

            'payments.view',
            'payments.create',

            'customers.view',
            'customers.create',
            'customers.update',

            'suppliers.view',
            'suppliers.create',
            'suppliers.update',

            'cash_registers.view',
            'cash_sessions.open',
            'cash_sessions.close',

            'employees.view',

            'expenses.view',
            'expenses.create',

            'reports.view',
        ]);

        Role::findByName('cashier')->syncPermissions([
            'products.view',
            'categories.view',

            'stock.view',

            'sales.view',
            'sales.create',

            'payments.view',
            'payments.create',

            'customers.view',
            'customers.create',
            'customers.update',

            'cash_registers.view',
            'cash_sessions.open',
            'cash_sessions.close',
        ]);

        Role::findByName('stock_manager')->syncPermissions([
            'products.view',
            'products.create',
            'products.update',

            'categories.view',
            'categories.create',
            'categories.update',

            'stock.view',
            'stock.create',
            'stock.adjust',
            'stock.transfer',

            'suppliers.view',
            'suppliers.create',
            'suppliers.update',
        ]);
    }
}
