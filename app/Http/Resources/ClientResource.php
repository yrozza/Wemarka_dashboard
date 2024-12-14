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
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->client_name,
            'age' => $this->client_age,
            'email' => $this->client_email,
            'phone_number' => $this->client_phonenumber,
            'area' => $this->client_area,
            'city' => $this->client_city,
            'source_id' => $this->source_id,
        ];
    }
}
