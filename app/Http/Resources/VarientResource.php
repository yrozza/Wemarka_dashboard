<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VarientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ID' => $this->id,
            'Product'=> $this->product ? $this->product->Product_name : null ,
            'color' => $this->color,
            'volume' => $this->volume,
            'varient'=>$this->varient,
            'Pcode'=>$this->Pcode,
            'weight'=>$this->weight,
            'product_image'=>$this->product_image,
            'stock'=>$this->stock
        ];
    }
}
