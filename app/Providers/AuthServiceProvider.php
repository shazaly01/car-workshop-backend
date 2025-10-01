<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate; // <-- أزل التعليق أو أضفه
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Models\Diagnosis;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\Payment;
use App\Policies\ClientPolicy;
use App\Policies\VehiclePolicy;
use App\Policies\WorkOrderPolicy;
use App\Policies\DiagnosisPolicy;
use App\Policies\QuotationPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\PaymentPolicy;
use App\Models\CatalogItem;
use App\Policies\CatalogItemPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
       Client::class    => ClientPolicy::class,
        Vehicle::class   => VehiclePolicy::class,
        WorkOrder::class => WorkOrderPolicy::class,
        Diagnosis::class => DiagnosisPolicy::class,
        Quotation::class => QuotationPolicy::class,
        Invoice::class   => InvoicePolicy::class,
        Payment::class   => PaymentPolicy::class,
        CatalogItem::class => CatalogItemPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // --- أضف هذا الكود ---
        // هذا الكود يمنح المستخدم الذي لديه دور 'admin' كل الصلاحيات تلقائياً
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });
        // --- نهاية الكود المضاف ---
    }
}
