<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'Brand_name' => $this->Brand_name,
            'Active' => $this->Active ? 'Active' : 'Not active',
            'Company_address' => $this->Company_address
        ];
    }
}
