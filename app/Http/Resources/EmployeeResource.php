<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'First Name' => $this->first_name,
            'Last Name' => $this->last_name,
            'Phone number' => $this->phone_number,
            'Email' => $this->email,
            'Role' => $this->role,
            'Profile Pic' => $this->profile_pic ? url('storage/' . $this->profile_pic) : 'Not provided',
        ];

    }
}
