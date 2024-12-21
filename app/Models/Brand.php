<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class brand extends Model
{
    protected $table = 'brands';
    protected $fillable =[
        'Brand_name',
        'Active',
        'Company_name'
    ];
}
