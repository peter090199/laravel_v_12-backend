<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagibigContribution extends Model
{
    protected $table = 'pagibigtables';

    protected $fillable = [
        'SalaryFrom',
        'SalaryTo',
        'EE',
        'ER',
        'RecordStatus',
    ];

    protected $casts = [
        'SalaryFrom' => 'float',
        'SalaryTo'   => 'float',
        'EE'         => 'float',
        'ER'         => 'float',
    ];
}
