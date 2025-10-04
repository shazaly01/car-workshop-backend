<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('list users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // السماح للمستخدم بعرض ملفه الشخصي، أو إذا كان يملك صلاحية عرض المستخدمين
        return $user->id === $model->id || $user->hasPermissionTo('view users');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create users');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // السماح للمستخدم بتحديث ملفه الشخصي، أو إذا كان يملك صلاحية تعديل المستخدمين
        return $user->id === $model->id || $user->hasPermissionTo('edit users');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // منع المستخدم من حذف نفسه
        if ($user->id === $model->id) {
            return false;
        }

        // منع حذف أي مدير آخر
        if ($model->hasRole('admin')) {
            return false;
        }

        // التحقق من الصلاحية
        return $user->hasPermissionTo('delete users');
    }
}
