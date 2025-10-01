<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

class DiagnosisPolicy
{
    /**
     * صلاحية مخصصة لإضافة تشخيص لأمر عمل.
     */
    public function create(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasPermissionTo('add diagnosis');
    }
}
