<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class PaymentPolicy
{
    public function create(User $user, Invoice $invoice): bool
    {
        return $user->hasPermissionTo('create payments');
    }
}
