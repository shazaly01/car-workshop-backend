<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the revenue report.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function viewRevenue(User $user): bool
    {
        // The user can view the revenue report if they have the 'view reports' permission.
        return $user->hasPermissionTo('view reports');
    }
}
