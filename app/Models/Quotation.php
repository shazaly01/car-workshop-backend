<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_id',
        'number',
        'issue_date',
        'expiry_date',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'notes',
    ];

    /**
     * Get the work order that this quotation belongs to.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Get all of the items for the Quotation.
     */
    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }
}
