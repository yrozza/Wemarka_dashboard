<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'variant_id' => $this->varient->id,
            'variant_name' => $this->varient->name,
            'product_name' => $this->varient->product->name,
            'quantity' => $this->quantity,
        ];
    }
}
