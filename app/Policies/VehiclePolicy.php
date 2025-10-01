<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('list vehicles');
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return $user->hasPermissionTo('view vehicles');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create vehicles');
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->hasPermissionTo('edit vehicles');
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->hasPermissionTo('delete vehicles');
    }
}
