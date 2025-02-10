<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray($request)
    {
        return [
            'Area' => $this->Area_name,
            'Active' => $this->active ? 'Active' : 'Not active',
            'Price' => $this->Price,
            'City' => $this->city ? $this->city->City_name : null, // Include city name
        ];
    }

    
}
