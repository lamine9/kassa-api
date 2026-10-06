<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Seeder;

class CatalogueTestSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = \App\Models\Tenant::where('slug', 'kassa-demo')->firstOrFail();

        app(TenantContext::class)->setTenant($tenant);

        Unit::create([
            'name' => 'Pièce',
            'symbol' => 'pcs',
            'decimal_places' => 0,
        ]);

        Unit::create([
            'name' => 'Kilogramme',
            'symbol' => 'kg',
            'decimal_places' => 3,
        ]);
    }
}
