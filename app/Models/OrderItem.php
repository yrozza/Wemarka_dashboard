<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'varient_id',
        'quantity',
        'price',
    ];
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }


    // One OrderItem belongs to one Variant
    public function varient()
    {
        return $this->belongsTo(Varient::class);
    }
}
