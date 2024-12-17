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
            'Employee_name' => $this->Employee_name,
            'Employee_phonenumber' => $this->Employee_phonenumber,
            'Employee_email' => $this->Employee_email,
            'Employee_role' => $this->Employee_role,
        ];
    }
}
