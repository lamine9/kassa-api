<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;
use App\Models\User;
use RuntimeException;

class TenantContext
{
    private ?Tenant $tenant = null;

    public function setTenant(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function setFromUser(User $user): void
    {
        $tenant = $user->tenant;

        if (!$tenant) {
            throw new RuntimeException('L’utilisateur n’est associé à aucune entreprise.');
        }

        if (!$tenant->is_active) {
            throw new RuntimeException('L’entreprise est inactive.');
        }

        $this->setTenant($tenant);
    }

    public function tenant(): Tenant
    {
        if (!$this->tenant) {
            throw new RuntimeException('Aucun tenant n’est actuellement défini.');
        }

        return $this->tenant;
    }

    public function id(): string
    {
        return $this->tenant()->id;
    }

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    public function clear(): void
    {
        $this->tenant = null;
    }
}
