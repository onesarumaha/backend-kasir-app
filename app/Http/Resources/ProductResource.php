<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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

            'code' => $this->code,

            'barcode' => $this->barcode,

            'name' => $this->name,

            'purchase_price' => $this->purchase_price,

            'selling_price' => $this->selling_price,

            'stock' => $this->stock,

            'minimum_stock' => $this->minimum_stock,

            'unit' => $this->unit,

            'image' => $this->image,

            'status' => $this->status,

            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
        ];
  
    }
}
