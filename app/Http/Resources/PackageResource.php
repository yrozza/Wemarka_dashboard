<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    public function toArray($request)
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'variants' => $this->whenLoaded('varients', function () {
            return $this->varients->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'color' => $variant->color,
                    'volume' => $variant->volume,
                    'weight' => $variant->weight,
                    'product_name' => $variant->product->Product_name,
                ];
            });
        }),
    ];
}
}
