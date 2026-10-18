<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhilhealthTable extends Model
{
    protected $table = 'philhealthtables';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'SalaryFrom',
        'SalaryTo',
        'EmployeeRate',
        'EmployerRate',
        'PayrollType',
        'RecordStatus',
    ];

    protected $casts = [
        'SalaryFrom'   => 'float',
        'SalaryTo'     => 'float',
        'EmployeeRate' => 'float',
        'EmployerRate' => 'float',
    ];
}
