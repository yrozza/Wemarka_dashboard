<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->client_name,
            'age' => $this->client_age,
            'email' => $this->client_email,
            'phone_number' => $this->client_phonenumber,
            'area' => $this->area_name ?? 'Area not provided', // Fetch from relationship
            'city' => $this->city_name?? 'City not provided', // Fetch from relationship
            'source_name' => $this->source?->Source_name ?? 'Unknown Source', // Handle NULL case
        ];
    }


}
