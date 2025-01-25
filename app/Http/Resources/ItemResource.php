<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
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
            'variant_id' => $this->varient_id,
            'quantity' => $this->quantity,
            'price_per_quantity' => $this->price,
            'total_price' => $this->order->total_price,
            'product_name' => $this->varient->product->Product_name, 
            'brand' => $this->varient->product->brand->Brand_name,  
            'category' => $this->varient->product->category->Category,  
            'variant_details' => [
                'color' => $this->varient->color,
                'volume' => $this->varient->volume,
                'pcode' => $this->varient->Pcode,
                'weight' => $this->varient->weight,
            ]
        ];
    }
}
