<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceRevision extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_revisions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_id',
        'user_id',
        'old_data',
        'new_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_data' => 'array', // تحويل حقل JSON إلى مصفوفة PHP تلقائياً
        'new_data' => 'array', // تحويل حقل JSON إلى مصفوفة PHP تلقائياً
    ];

    /**
     * Get the invoice that this revision belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the user who made this revision.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
