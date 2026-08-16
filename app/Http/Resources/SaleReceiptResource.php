<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleReceiptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'transaction_date' => $this->transaction_date?->format('Y-m-d H:i:s'),
            'cashier' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],

            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,

                    'product' => [
                        'id' => $item->product?->id,
                        'name' => $item->product?->name,
                    ],

                    'price' => (float) $item->price,
                    'qty' => $item->qty,
                    'subtotal' => (float) $item->subtotal,
                ];
            }),

            'total_item' => $this->total_item,

            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'tax' => (float) $this->tax,
            'grand_total' => (float) $this->grand_total,

            'payment' => (float) $this->payment,
            'change' => (float) $this->change,

            'payment_method' => $this->payment_method,

            'status' => $this->status,

            'note' => $this->note,
        ];
    
    }
}
