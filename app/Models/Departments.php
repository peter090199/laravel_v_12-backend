<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departments extends Model
{
    protected $table = 'departments';

    // Set to true if your table has created_at / updated_at
    public $timestamps = false;

    protected $fillable = [
        'Department',
        'AllowOvertime',
        'SaturdayHalfDay',
        'PayrollType',
        'RecordStatus',
    ];

    protected $casts = [
        'AllowOvertime'   => 'boolean',
        'SaturdayHalfDay' => 'boolean',
    ];
}
