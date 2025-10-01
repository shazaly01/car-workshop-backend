<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    // لا يوجد softDeletes هنا، فعملية الدفع لا تُحذف عادةً بل يتم إلغاؤها بطرق محاسبية أخرى
    // (لكن يمكن إضافتها إذا تطلب منطق العمل ذلك)

    protected $fillable = [
        'invoice_id',
        'received_by_user_id',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date', // تحويل حقل التاريخ إلى كائن Carbon تلقائياً
    ];

    /**
     * Get the invoice that this payment is for.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the user who received the payment.
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }
}
