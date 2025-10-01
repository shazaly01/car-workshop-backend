<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    // كما في بنود عرض السعر، لا يوجد هنا softDeletes أو timestamps
    public $timestamps = false;

    protected $fillable = [
        'invoice_id',
        'catalog_item_id',
        'description',
        'type',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the invoice that this item belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }



    /**
     * Get the catalog item associated with the invoice item.
     */
    public function catalogItem(): BelongsTo // <-- 2. العلاقة الجديدة
    {
        return $this->belongsTo(CatalogItem::class);
    }
}
