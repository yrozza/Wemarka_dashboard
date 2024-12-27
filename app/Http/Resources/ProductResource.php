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
            'ID' =>$this->id,
            'Product name' =>$this->Product_name,
            'Product description' => $this->Product_description,
            'Brand' => $this->brand ? $this->brand->Brand_name : null,
            'Category' => $this->category ? $this->category->Category : null,
        ];
    }
}
