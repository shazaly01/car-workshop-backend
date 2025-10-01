<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnosis extends Model
{
    use HasFactory;

    // لا يوجد softDeletes هنا لأن التشخيص يُحذف مع أمر العمل

    protected $fillable = [
        'work_order_id',
        'technician_id',
        'obd_codes',
        'manual_inspection_results',
        'proposed_repairs',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'obd_codes' => 'array', // مهم جداً: لتحويل حقل JSON إلى مصفوفة PHP تلقائياً
    ];

    /**
     * Get the work order that this diagnosis belongs to.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Get the technician (user) who performed the diagnosis.
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
