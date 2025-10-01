<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('list work-orders');
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasPermissionTo('view work-orders');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create work-orders');
    }

    public function update(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasPermissionTo('edit work-orders');
    }

    public function delete(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasPermissionTo('delete work-orders');
    }

    /**
     * صلاحية مخصصة لتغيير حالة أمر العمل.
     */
    public function changeStatus(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasPermissionTo('change work-order status');
    }
}
