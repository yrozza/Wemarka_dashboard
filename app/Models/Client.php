<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class client extends Model
{
    public function orders()
    {
        return $this->hasMany(Order::class); // Client has many orders
    }
}
