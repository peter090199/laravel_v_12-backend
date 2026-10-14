<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlNumber extends Model
{
    protected $table = 'control_numbers';

    protected $fillable = [
        'code',
        'prefix',
        'last_number',
        'padding',
    ];

    protected $casts = [
        'last_number' => 'integer',
        'padding'     => 'integer',
    ];
}