<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = ['varient_id', 'image_url'];



    public function variant()
    {
        return $this->belongsTo(Varient::class);
    }
}
