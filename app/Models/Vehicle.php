<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // استيراد نوع العلاقة للوضوح
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'vin',
        'plate_number',
        'make',
        'model',
        'year',
        'color',
        'mileage',
    ];

    /**
     * Get the client that owns the vehicle.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }


     public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }
}
