<?php

namespace App\Policies;

use App\Models\Invoice; // <-- أضف هذا
use App\Models\User;
use App\Models\WorkOrder;

class InvoicePolicy
{
    public function create(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasPermissionTo('create invoices');
    }

    // /**
    //  * Determine whether the user can void the model.
    //  *
    //  * @param  \App\Models\User  $user
    //  * @param  \App\Models\Invoice  $invoice
    //  * @return bool
    //  */
    // public function void(User $user, Invoice $invoice): bool
    // {
    //     // اسمح بالإلغاء فقط إذا كان المستخدم يملك الصلاحية
    //     // والفاتورة ليست مدفوعة أو ملغاة بالفعل.
    //     $isNotPaid = !in_array($invoice->status, ['paid', 'partially_paid', 'voided']);

    //     return $user->hasPermissionTo('void invoices') && $isNotPaid;
    // }




    public function void(User $user, Invoice $invoice): bool
    {
        // تحقق فقط مما إذا كان المستخدم يملك الصلاحية
        return $user->hasPermissionTo('void invoices');
    }
}
