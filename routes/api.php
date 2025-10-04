<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- استيراد جميع المتحكمات المستخدمة ---
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CatalogItemController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\WorkOrderStatusController;
// *** START: IMPORT NEW CONTROLLER ***
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
// *** END: IMPORT NEW CONTROLLER ***

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- المسارات العامة (لا تتطلب تسجيل دخول) ---
Route::post('/login', [LoginController::class, 'login']);


// --- مجموعة المسارات المحمية (تتطلب تسجيل دخول صالح) ---
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        // تحميل الأدوار والصلاحيات مع المستخدم
        $user = $request->user()->load('roles', 'permissions');
        return new \App\Http\Resources\UserResource($user);
    });

    // == لوحة التحكم (Dashboard) ==
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // *** START: NEW USER MANAGEMENT ROUTE ***
    // == إدارة المستخدمين (Users) ==
    Route::get('users', [UserController::class, 'index'])
        ->middleware('permission:view users') // حماية المسار بالصلاحية
        ->name('users.index');
    // *** END: NEW USER MANAGEMENT ROUTE ***

    // == إدارة الكتالوج (قطع الغيار والخدمات) ==
    Route::apiResource('catalog-items', CatalogItemController::class);

    // == إدارة العملاء (Clients) ==
    Route::apiResource('clients', ClientController::class);

    // == إدارة السيارات (Vehicles) ==
    Route::apiResource('vehicles', VehicleController::class);

    // == إدارة أوامر العمل (Work Orders) ==
    Route::apiResource('work-orders', WorkOrderController::class);
    Route::get('work-orders/{work_order}/print', [WorkOrderController::class, 'print'])->name('work-orders.print');
    Route::put('work-orders/{work_order}/status', [WorkOrderStatusController::class, 'update'])->name('work-orders.status.update');
    Route::post('work-orders/{work_order}/diagnoses', [DiagnosisController::class, 'store'])->name('work-orders.diagnoses.store');
    Route::put('diagnoses/{diagnosis}', [DiagnosisController::class, 'update'])->name('diagnoses.update');
    Route::post('work-orders/{work_order}/quotations', [QuotationController::class, 'store'])->name('work-orders.quotations.store');
    Route::put('quotations/{quotation}', [\App\Http\Controllers\QuotationController::class, 'update'])->name('quotations.update');
    Route::post('work-orders/{work_order}/invoices', [InvoiceController::class, 'store'])->name('work-orders.invoices.store');

    // == إدارة الفواتير (Invoices) ==
    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('invoices.payments.store');
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');

    // *** START: PROTECTED REPORT ROUTE ***
    // == التقارير (Reports) ==
   Route::get('reports/revenue', [\App\Http\Controllers\RevenueReportController::class, 'getRevenueSummary'])
        ->name('reports.revenue.summary');
    // *** END: PROTECTED REPORT ROUTE ***





 Route::apiResource('users', UserController::class);

// مسار لجلب قائمة الأدوار.
// سيتم التحقق من الصلاحيات داخل RoleController's __construct.
Route::apiResource('roles', RoleController::class); // <-- تحويله إلى apiResource
Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions']);
Route::get('permissions', [PermissionController::class, 'index']); // <-

});
