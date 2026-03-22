<?php

declare(strict_types=1);

namespace Modules\Identity\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Identity\Models\Permission;
use Modules\Identity\Models\Role;

class PermissionService
{
    public function userHasPermission(User $user, string $permissionSlug): bool
    {
        $permissions = $this->permissionsForUser($user);

        return $permissions->contains(
            fn (Permission $permission): bool => $permission->slug === $permissionSlug,
        );
    }

    /**
     * @return Collection<int, Permission>
     */
    public function permissionsForUser(User $user): Collection
    {
        /** @var Collection<int, Role> $roles */
        $roles = $user->roles()->with('permissions')->get();

        return $roles
            ->flatMap(
                fn (Role $role): Collection => $role->permissions,
            )
            ->unique('id')
            ->values();
    }
}
