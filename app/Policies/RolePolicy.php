<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * @var array
     */
    protected $protectedRoles = ['admin', 'receptionist', 'technician'];

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('list roles');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('view roles');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create roles');
    }

    public function update(User $user, Role $role): bool
    {
        // لا يمكن تعديل الأدوار الأساسية المحمية
        if (in_array($role->name, $this->protectedRoles)) {
            return false;
        }
        return $user->hasPermissionTo('edit roles');
    }

    public function delete(User $user, Role $role): bool
    {
        // لا يمكن حذف الأدوار الأساسية المحمية
        if (in_array($role->name, $this->protectedRoles)) {
            return false;
        }
        return $user->hasPermissionTo('delete roles');
    }
}
