<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Quotation;

class QuotationPolicy
{
    public function create(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasPermissionTo('create quotations');
    }


     /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Quotation $quotation
     * @return bool
     */
    public function update(User $user, Quotation $quotation): bool // <-- هذه هي الدالة المطلوبة
    {
        // سنفترض أن هناك صلاحية جديدة اسمها 'edit quotations'
        // أو يمكننا إعادة استخدام صلاحية موجودة مثل 'edit work-orders'
        return $user->hasPermissionTo('edit quotations');
    }
}
