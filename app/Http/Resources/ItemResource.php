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
            'product_name' => $this->varient->product->Product_name ?? 'unprovided',
            'brand' => $this->varient->product->brand->Brand_name ?? 'unprovided',
            'category' => $this->varient->product->category->Category ?? 'unprovided',
            'quantity' => $this->quantity ?? 'unprovided',
            'price_per_quantity' => $this->price ?? 'unprovided',
            'total_price' => $this->price * $this->quantity ?? 'unprovided',
            'variant_details' => [
                'color' => $this->varient->color ?? 'unprovided',
                'volume' => $this->varient->volume ?? 'unprovided',
                'weight' => $this->varient->weight ?? 'unprovided',
                'Origin' => $this->varient->product->Origin ?? 'unprovided',
                'Effect' => $this->varient->product->Effect ?? 'unprovided',
                'Benefit' => $this->varient->product->Benefit ?? 'unprovided',
                'Ingredients' => $this->varient->product->Ingredients ?? 'unprovided'
            ]

        ];
    }
}
