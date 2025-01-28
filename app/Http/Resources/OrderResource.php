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
            'client_phone' => $this->client_phone ,
            'addiontional_phone'=>$this->addiontional_phone,
            'cart_id' => $this->cart_id,
            'status' => $this->status,
            'total_price' => $this->total_price,
            'shipping_status' => $this->shipping_status,
            'area_name' => $this->area_name,  
            'city_name' => $this->city_name, 
            'Address' =>$this->Address,
            'order_items' => ItemResource::collection($this->whenLoaded('orderItems')), // Eager loaded order items
        ];
    }
}