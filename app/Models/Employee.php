<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    public $timestamps = false;

    protected $fillable= [
        'Employee_name',
        'Employee_phonenumber',
        'Employee_email',
        'Employee_role'
    ];

    protected $hidden = ['created_at', 'updated_at'];
}
