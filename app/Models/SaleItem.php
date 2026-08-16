<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

     protected $fillable = [
        'sale_id',
        'product_id',
        'price',
        'qty',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'qty' => 'integer',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * Item ini milik satu transaksi
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Item ini adalah product tertentu
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

}
