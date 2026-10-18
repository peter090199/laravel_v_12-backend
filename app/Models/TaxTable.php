<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxTable extends Model
{
    protected $table = 'taxtables';

    protected $primaryKey = 'id';

    protected $fillable = [
        'SalaryFrom',
        'SalaryTo',
        'FixedTax',
        'Percent',
        'PayrollType',
        'RecordStatus',
    ];

    protected $casts = [
        'SalaryFrom' => 'float',
        'SalaryTo'   => 'float',
        'FixedTax'   => 'float',
        'Percent'    => 'float',
    ];
}