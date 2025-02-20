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
    public function toArray($request)
    {
        $isPackage = $this->package_id !== null;

        // Base response
        $response = [];

        // If it's a package, show the package name and its products
        if ($isPackage) {
            $response['package_name'] = $this->package->name;

            $response['items'] = $this->package->varients->map(function ($variant) {
                return array_filter([
                    'product_name' => $variant->product->Product_name ?? null, // Access product via `product`
                    'brand' => $variant->product->brand->Brand_name ?? null,
                    'category' => $variant->product->category->Category ?? null,
                    'variant_details' => array_filter([
                        'color' => $variant->color ?? null,  // Directly from Variant
                        'volume' => $variant->volume ?? null,
                        'weight' => $variant->weight ?? null,
                        'Origin' => $variant->product->Origin ?? null,
                        'Effect' => $variant->product->Effect ?? null,
                        'Benefit' => $variant->product->Benefit ?? null,
                        'Ingredients' => $variant->product->Ingredients ?? null,
                    ]),
                ]);
            });


            $response['total_price'] = $this->package->price;
        } else {
            // If it's a single variant
            $response = array_filter([
                'product_name' => $this->varient->product->Product_name ?? null,
                'brand' => $this->varient->product->brand->Brand_name ?? null,
                'category' => $this->varient->product->category->Category ?? null,
                'quantity' => $this->quantity ?? null,
                'price_per_quantity' => $this->price ?? null,
                'total_price' => ($this->price * $this->quantity) ?? null,
                'variant_details' => array_filter([
                    'color' => $this->varient->color ?? null,
                    'volume' => $this->varient->volume ?? null,
                    'weight' => $this->varient->weight ?? null,
                    'Origin' => $this->varient->product->Origin ?? null,
                    'Effect' => $this->varient->product->Effect ?? null,
                    'Benefit' => $this->varient->product->Benefit ?? null,
                    'Ingredients' => $this->varient->product->Ingredients ?? null,
                ]),
            ]);
        }

        return $response;
    }

}
