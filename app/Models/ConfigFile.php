<?php
// app/Models/ConfigFile.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigFile extends Model
{
    protected $table = 'configfiles';
    protected $primaryKey = 'ConfigID';   // <-- change to your real primary key column
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'NotifyAfter'       => 'integer',
        'FirstPayrollFrom'  => 'integer',
        'FirstPayrollTo'    => 'integer',
        'SecondPayrollFrom' => 'integer',
        'SecondPayrollTo'   => 'integer',
        'WorkingDays'       => 'integer',
        'YearlyWorkingDays' => 'float',
    ];

    // yes/no columns (Angular key == DB column with a lowercase first letter)
    public const FLAGS = [
        'conBasicPay', 'conAllowance', 'conTardiness', 'conOtherIncome', 'conOtherDeduction',
        'conLoans', 'conOvertime', 'conHoliday', 'conRestday', 'conNightPremDiff',
        'taxBasicPay', 'taxAllowance', 'taxTardines', 'taxContributions',
        'taxOvertime', 'taxHoliday', 'taxRestday', 'taxNightPremDiff',
        'basicTaxContribution',
    ];

    public const FIELDS = [
        'notifyAfter', 'weeklyCutOffDay',
        'firstPayrollFrom', 'firstPayrollTo', 'secondPayrollFrom', 'secondPayrollTo',
        'workingDays', 'yearlyWorkingDays', 'scheduleType',
        ...self::FLAGS,
    ];

    /** Validation rules, keyed by the camelCase names the Angular app sends. */
    public static function rules(bool $partial = false): array
    {
        $req = $partial ? 'sometimes' : 'required';

        $rules = [
            'notifyAfter'       => [$req, 'integer', 'min:0'],
            'weeklyCutOffDay'   => ['sometimes', 'nullable', 'string', 'max:20'],
            'firstPayrollFrom'  => [$req, 'integer'],
            'firstPayrollTo'    => [$req, 'integer'],
            'secondPayrollFrom' => [$req, 'integer'],
            'secondPayrollTo'   => [$req, 'integer'],
            'workingDays'       => [$req, 'integer', 'min:0'],
            'yearlyWorkingDays' => [$req, 'numeric', 'min:0'],
            'scheduleType'      => ['sometimes', 'nullable', 'string', 'max:50'],
        ];

        foreach (self::FLAGS as $flag) {
            $rules[$flag] = [$req, 'in:yes,no'];
        }

        return $rules;
    }

    /** camelCase request data -> DB columns (null strings become '' like the old app). */
    public static function toColumns(array $data): array
    {
        return collect($data)
            ->mapWithKeys(fn ($v, $k) => [ucfirst($k) => $v ?? ''])
            ->all();
    }

    /** DB row -> camelCase array for Angular. */
    public function toApi(): array
    {
        return collect(self::FIELDS)
            ->mapWithKeys(fn ($f) => [$f => $this->{ucfirst($f)}])
            ->prepend($this->getKey(), 'id')
            ->all();
    }
}