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
            'ID' => $this->id,
            'Product name' => $this->Product_name,
            'Product description' => $this->Product_description,
            'Origin' => $this->Origin,
            'Benefit' => $this->Benefit,
            'Effect' => $this->Effect,
            'Ingredients' => $this->Ingredients,
            'Supplier' => $this->Supplier,
            'Category' => $this->Category_name,
            'Brand' => $this->Brand_name,
            'Subcategory' => $this->Subcategory,
            'Tags' => $this->Tags,
        ];

    }
}
