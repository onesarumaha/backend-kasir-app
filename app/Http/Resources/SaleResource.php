<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
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

            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],

            'transaction_date' => $this->transaction_date,

            'total_item' => $this->total_item,

            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'grand_total' => $this->grand_total,

            'payment' => $this->payment,
            'change' => $this->change,

            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'note' => $this->note,

            'items' => SaleItemResource::collection(
                $this->whenLoaded('items')
            ),
            'tenant' => $this->whenLoaded('tenant', function () {
                return [
                    'id' => $this->tenant->id,
                    'name' => $this->tenant->name,
                    'address' => $this->tenant->address,
                    'logo_url' => $this->tenant->logo_url ?? null,
                ];
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
