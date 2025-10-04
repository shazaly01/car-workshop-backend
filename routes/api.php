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
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RevenueReportController; // تم التأكد من استيراده

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- المسارات العامة (لا تتطلب تسجيل دخول) ---
Route::post('/login', [LoginController::class, 'login']);


// --- مجموعة المسارات المحمية (تتطلب تسجيل دخول صالح) ---
Route::middleware('auth:sanctum')->group(function () {

    // مسار لجلب بيانات المستخدم المسجل حاليًا مع أدواره وصلاحياته
    Route::get('/user', function (Request $request) {
        $user = $request->user()->load('roles', 'permissions');
        return new \App\Http\Resources\UserResource($user);
    });

    // == لوحة التحكم (Dashboard) ==
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // == إدارة الموارد (apiResources) ==
    // هذه المسارات محمية تلقائيًا بواسطة 'auth:sanctum'
    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('catalog-items', CatalogItemController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('vehicles', VehicleController::class);
    Route::apiResource('work-orders', WorkOrderController::class);
    Route::apiResource('invoices', InvoiceController::class)->only(['index', 'show', 'destroy']); // تحديد المسارات المطلوبة فقط

    // == المسارات المخصصة (Custom Routes) ==

    // مسارات مخصصة للأدوار والصلاحيات
    Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');

    // مسارات مخصصة لأوامر العمل
    Route::get('work-orders/{work_order}/print', [WorkOrderController::class, 'print'])->name('work-orders.print');
    Route::put('work-orders/{work_order}/status', [WorkOrderStatusController::class, 'update'])->name('work-orders.status.update');
    Route::post('work-orders/{work_order}/diagnoses', [DiagnosisController::class, 'store'])->name('work-orders.diagnoses.store');
    Route::put('diagnoses/{diagnosis}', [DiagnosisController::class, 'update'])->name('diagnoses.update');
    Route::post('work-orders/{work_order}/quotations', [QuotationController::class, 'store'])->name('work-orders.quotations.store');
    Route::put('quotations/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
    Route::post('work-orders/{work_order}/invoices', [InvoiceController::class, 'store'])->name('work-orders.invoices.store');

    // مسارات مخصصة للفواتير والمدفوعات
    Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('invoices.payments.store');
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // مسارات مخصصة للتقارير
    Route::get('reports/revenue', [RevenueReportController::class, 'getRevenueSummary'])->name('reports.revenue.summary');

});
