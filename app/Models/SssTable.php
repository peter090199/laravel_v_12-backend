<?php
// app/Models/SssTable.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SssTable extends Model
{
    protected $table = 'ssstables';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'SalaryFrom',
        'SalaryTo',
        'ER',
        'EE',
        'EC',
        'RecordStatus',
    ];

    protected $casts = [
        'SalaryFrom' => 'float',
        'SalaryTo'   => 'float',
        'ER'         => 'float',
        'EE'         => 'float',
    ];
}
