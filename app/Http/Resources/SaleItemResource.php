<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
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

            'product' => [
                'id' => $this->product?->id,
                'name' => $this->product?->name,
                'price' => $this->product?->price,
            ],

            'price' => $this->price,
            'qty' => $this->qty,
            'subtotal' => $this->subtotal,
        ];
    }
}
