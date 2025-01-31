<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\ItemResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'client_name' => $this->client_name,
            'client_phone' => $this->client_phone,
            'additional_phone' => $this->additional_phone,
            'area_name' => $this->area_name,
            'city_name' => $this->city_name,
            'Address' => $this->Address,
            'cart_id' => $this->cart_id,
            'status' => $this->status,
            'shipping_status' => $this->shipping_status,
            'Cost_shipping_price' => $this->Cost_shipping_price,
            'Shipping_price' => $this->Shipping_price,
            'packing' => $this->packing ? 'Provided' : 'Not provided', // "Provided" if true, else "Not provided"
            'packing_price' => $this->packing ? $this->packing_price : 0, // 0 if false
            'total_price' => $this->total_price,
            'order_items' => ItemResource::collection($this->whenLoaded('orderItems')),
        ];



    }
}