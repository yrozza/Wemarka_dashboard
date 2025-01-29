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
            'product_name' => $this->varient->product->Product_name,
            'brand' => $this->varient->product->brand->Brand_name,
            'category' => $this->varient->product->category->Category, 
            'quantity' => $this->quantity,
            'price_per_quantity' => $this->price,
            'total_price' => $this->order->total_price,
            'variant_details' => [
                'color' => $this->varient->color,
                'volume' => $this->varient->volume,
                'weight' => $this->varient->weight,
                'Origin'  =>$this->varient->product->Origin,
                'Effect'  => $this->varient->product->Effect,
                'Benefit' =>$this->varient->product->Benefit,
                'Ingredients' =>$this->varient->product->Ingredients
            ]
        ];
    }
}
