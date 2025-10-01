<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    use HasFactory;

    // لا يوجد softDeletes هنا، لأن البنود تُحذف مع عرض السعر الرئيسي (onDelete cascade)
    // لا يوجد timestamps أيضاً لأننا لم نضفها في الجدول، يمكن إضافتها إذا احتجنا تتبع تعديل البنود

    public $timestamps = false; // لإعلام Laravel بعدم وجود حقلي created_at/updated_at

    protected $fillable = [
        'quotation_id',
        'catalog_item_id',
        'description',
        'type',
        'quantity',
        'unit_price',
        'total_price',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the quotation that this item belongs to.
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }


     /**
     * Get the catalog item associated with the quotation item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function catalogItem(): BelongsTo // <-- هذه هي العلاقة المطلوبة
    {
        return $this->belongsTo(CatalogItem::class);
    }
}
