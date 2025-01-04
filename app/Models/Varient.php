<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Varient extends Model
{
    protected $fillable = [
        'color',
        'volume',
        'varient',
        'Pcode',
        'weight',
        'price',
        'product_image',
        'stock',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
