<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    public function orders (){
        return $this->belongsToMany(Order::class)->withPivot('quantity', 'price');
    }
}
