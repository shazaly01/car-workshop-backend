<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientPolicy
{
    /**
     * Determine whether the user can view any models.
     * (Corresponds to the 'index' method in the controller)
     */
    public function viewAny(User $user): bool
    {
        // هل يملك المستخدم صلاحية "عرض قائمة العملاء"؟
        return $user->hasPermissionTo('list clients');
    }

    /**
     * Determine whether the user can view the model.
     * (Corresponds to the 'show' method)
     */
    public function view(User $user, Client $client): bool
    {
        // هل يملك المستخدم صلاحية "عرض تفاصيل العملاء"؟
        return $user->hasPermissionTo('view clients');
    }

    /**
     * Determine whether the user can create models.
     * (Corresponds to the 'store' method)
     */
    public function create(User $user): bool
    {
        // هل يملك المستخدم صلاحية "إنشاء عملاء"؟
        return $user->hasPermissionTo('create clients');
    }

    /**
     * Determine whether the user can update the model.
     * (Corresponds to the 'update' method)
     */
    public function update(User $user, Client $client): bool
    {
        // هل يملك المستخدم صلاحية "تعديل العملاء"؟
        return $user->hasPermissionTo('edit clients');
    }

    /**
     * Determine whether the user can delete the model.
     * (Corresponds to the 'destroy' method)
     */
    public function delete(User $user, Client $client): bool
    {
        // هل يملك المستخدم صلاحية "حذف العملاء"؟
        return $user->hasPermissionTo('delete clients');
    }

    /**
     * Determine whether the user can restore the model.
     * (Not used by us currently, but good to have)
     */
    public function restore(User $user, Client $client): bool
    {
        // يمكن ربطها بنفس صلاحية التعديل أو إنشاء صلاحية جديدة
        return $user->hasPermissionTo('edit clients');
    }

    /**
     * Determine whether the user can permanently delete the model.
     * (Not used by us currently)
     */
    public function forceDelete(User $user, Client $client): bool
    {
        // هذه صلاحية خطيرة، عادة ما تكون للمدير فقط
        return $user->hasRole('admin');
    }
}
