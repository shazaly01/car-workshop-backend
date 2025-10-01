<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // استيراد نوع العلاقة للوضوح
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'client_type',
    ];

    /**
     * Get all of the vehicles for the Client.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }


     public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }
}
