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

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| هنا يتم تسجيل جميع مسارات الواجهة البرمجية (API) للتطبيق.
|
*/

// --- المسارات العامة (لا تتطلب تسجيل دخول) ---

// مسار تسجيل الدخول
Route::post('/login', [LoginController::class, 'login']);


// --- مجموعة المسارات المحمية (تتطلب تسجيل دخول صالح) ---
Route::middleware('auth:sanctum')->group(function () {

    // مسار جلب بيانات المستخدم المسجل حالياً
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // == لوحة التحكم (Dashboard) ==
    // GET /api/dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // == إدارة الكتالوج (قطع الغيار والخدمات) ==
    // GET, POST, PUT, DELETE /api/catalog-items
    // ملاحظة: هذا المسار محمي أيضاً بـ Policy داخل المتحكم
    Route::apiResource('catalog-items', CatalogItemController::class);

    // == إدارة العملاء (Clients) ==
    // GET, POST, PUT, DELETE /api/clients
    Route::apiResource('clients', ClientController::class);

    // == إدارة السيارات (Vehicles) ==
    // GET, POST, PUT, DELETE /api/vehicles
    Route::apiResource('vehicles', VehicleController::class);

    // == إدارة أوامر العمل (Work Orders) ==
    // GET, POST, PUT, DELETE /api/work-orders
    Route::apiResource('work-orders', WorkOrderController::class);

    // -- المسارات المتفرعة من أوامر العمل --

    // تحديث حالة أمر العمل
    // PUT /api/work-orders/{work_order}/status
    Route::put('work-orders/{work_order}/status', [WorkOrderStatusController::class, 'update'])
        ->name('work-orders.status.update');

    // إضافة تشخيص لأمر عمل
    // POST /api/work-orders/{work_order}/diagnoses
    Route::post('work-orders/{work_order}/diagnoses', [DiagnosisController::class, 'store'])
        ->name('work-orders.diagnoses.store');

    // إضافة عرض سعر لأمر عمل
    // POST /api/work-orders/{work_order}/quotations
    Route::post('work-orders/{work_order}/quotations', [QuotationController::class, 'store'])
        ->name('work-orders.quotations.store');

        Route::put('quotations/{quotation}', [\App\Http\Controllers\QuotationController::class, 'update'])->name('quotations.update');

    // إنشاء فاتورة من أمر عمل
    // POST /api/work-orders/{work_order}/invoices
    Route::post('work-orders/{work_order}/invoices', [InvoiceController::class, 'store'])
        ->name('work-orders.invoices.store');

    // == إدارة الفواتير (Invoices) ==
    // يمكن إضافة متحكم موردي كامل للفواتير إذا احتجنا لعرضها وتعديلها بشكل مستقل
    // Route::apiResource('invoices', InvoiceController::class);
 Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    // -- المسارات المتفرعة من الفواتير --

    // إضافة دفعة لفاتورة
    // POST /api/invoices/{invoice}/payments
    Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])
        ->name('invoices.payments.store');




});
