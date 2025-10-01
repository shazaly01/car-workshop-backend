<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'vehicle_id',
        'created_by_user_id',
        'number',
        'client_complaint',
        'initial_inspection_notes',
        'status',
    ];

    /**
     * Get the client associated with the work order.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the vehicle associated with the work order.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who created the work order.
     */
    public function createdBy(): BelongsTo
    {
        // نحدد اسم المفتاح الأجنبي يدوياً لأنه لا يتبع العرف القياسي (user_id)
        return $this->belongsTo(User::class, 'created_by_user_id');
    }


    public function diagnosis(): HasOne
    {
        return $this->hasOne(Diagnosis::class);
    }


     public function quotation(): HasOne
    {
        return $this->hasOne(Quotation::class);
    }



       public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
