<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

     protected $fillable = [
        'invoice_number',
        'user_id',
        'transaction_date',
        'total_item',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'payment',
        'change',
        'payment_method',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'total_item' => 'integer',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'payment' => 'decimal:2',
            'change' => 'decimal:2',
        ];
    }

    /**
     * Sale dilakukan oleh satu user/kasir
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Sale memiliki banyak item
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

}
