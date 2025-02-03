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
            'Sku code'=>$this->SKU_code,
            'weight'=>$this->weight,
            'Stock'=>$this->stock,
            'Stock Status' =>$this->Stock_status,
            'Cost price' =>$this->cost_price,
            'Price' =>$this->price, 
            'Selling Price'=> $this->Selling_price
        ];
    }
}
