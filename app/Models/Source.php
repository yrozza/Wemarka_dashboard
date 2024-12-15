<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    public function clients(){
        return $this->hasMany(client::class);
    }
}
