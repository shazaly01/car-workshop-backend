<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\WorkOrder;
use App\Models\User;
use App\Models\CatalogItem;

class WorkOrderLifecycleSeeder extends Seeder
{
    public function run(): void
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $receptionist = User::where('username', 'reception')->first();
        $technician = User::where('username', 'tech')->first();
        $catalogItems = CatalogItem::all();
        $clientsWithVehicles = Client::with('vehicles')->has('vehicles')->get()->take(10); // نأخذ 10 عملاء لديهم مركبات

        foreach ($clientsWithVehicles as $client) {
            $vehicle = $client->vehicles->first();

            // استخدام Transaction لكل دورة عمل لضمان سلامة البيانات
            DB::transaction(function () use ($client, $vehicle, $receptionist, $technician, $catalogItems) {
                // 1. إنشاء أمر عمل
                $workOrder = WorkOrder::create([
                    'client_id' => $client->id,
                    'vehicle_id' => $vehicle->id,
                    'created_by_user_id' => $receptionist->id,
                    'number' => 'WO-' . date('Y') . '-' . str_pad(WorkOrder::count() + 1, 5, '0', STR_PAD_LEFT),
                    'client_complaint' => 'السيارة تصدر صوتاً غريباً عند التسارع.',
                    'status' => 'pending',
                ]);

                // 2. إضافة تشخيص
                $workOrder->diagnosis()->create([
                    'technician_id' => $technician->id,
                    'obd_codes' => ['P0301', 'P0420'],
                    'manual_inspection_results' => 'تم فحص المحرك بصرياً، ويوجد تسريب زيت بسيط.',
                    'proposed_repairs' => 'تغيير بواجي، فحص حساس الأكسجين.',
                ]);

                // 3. إنشاء عرض سعر
                $quotationItemsData = $catalogItems->where('type', 'part')->random(2)->merge(
                    $catalogItems->where('type', 'service')->random(1)
                );
                $subtotal = 0;
                $itemsToCreate = [];
                foreach ($quotationItemsData as $item) {
                    $quantity = 1;
                    $total = $item->unit_price * $quantity;
                    $subtotal += $total;
                    $itemsToCreate[] = [
                        'catalog_item_id' => $item->id,
                        'description' => $item->name,
                        'type' => $item->type,
                        'quantity' => $quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $total,
                    ];
                }
                $tax = $subtotal * 0.15;
                $totalAmount = $subtotal + $tax;

                $quotation = $workOrder->quotation()->create([
                    'number' => 'QT-' . date('Y') . '-' . str_pad($workOrder->id, 4, '0', STR_PAD_LEFT),
                    'issue_date' => now(),
                    'expiry_date' => now()->addDays(7),
                    'status' => 'sent',
                    'subtotal' => $subtotal,
                    'tax_amount' => $tax,
                    'total_amount' => $totalAmount,
                ]);
                $quotation->items()->createMany($itemsToCreate);

                // 4. إنشاء فاتورة (محاكاة لمتحكم الفواتير)
                $invoice = $workOrder->invoice()->create([
                    'client_id' => $client->id,
                    'number' => 'INV-' . date('Y') . '-' . str_pad($workOrder->id, 4, '0', STR_PAD_LEFT),
                    'issue_date' => now()->addDay(),
                    'due_date' => now()->addDays(16),
                    'status' => 'unpaid',
                    'subtotal' => $quotation->subtotal,
                    'tax_percentage' => 15.00,
                    'tax_amount' => $quotation->tax_amount,
                    'total_amount' => $quotation->total_amount,
                    'paid_amount' => 0,
                ]);
                $invoice->items()->createMany($itemsToCreate);
                $quotation->update(['status' => 'approved']);
                $workOrder->update(['status' => 'in_progress']);

                // 5. تسجيل دفعة (أو دفعتين)
                $paymentAmount = round($invoice->total_amount / 2, 2);
                $invoice->payments()->create([
                    'amount' => $paymentAmount,
                    'payment_date' => now()->addDay(),
                    'payment_method' => 'card',
                    'received_by_user_id' => $receptionist->id,
                ]);
                $invoice->paid_amount += $paymentAmount;
                $invoice->status = 'partially_paid';
                $invoice->save();
            });
        }
    }
}
