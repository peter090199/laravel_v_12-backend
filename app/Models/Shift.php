<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $table = 'shifts';

    protected $fillable = [
        'RecordStatus',
        'DutyHours',
        'LateAfter',
        'OvertimeAfter',
        'DutyFrom',
        'DutyTo',
        'DutyShift',
        'IsNightShift',
        'IsNextDayOut',
        'HasBreakShift',
        'BreakTimeout',
        'BreakTimein',
        'BreakShift',
        'FlexBreakMins',
        'HasLunchShift',
        'LunchTimeout',
        'LunchTimein',
        'LunchShift',
    ];

    protected $casts = [
        'DutyHours'     => 'float',
        'IsNightShift'  => 'boolean',
        'IsNextDayOut'  => 'boolean',
        'HasBreakShift' => 'boolean',
        'HasLunchShift' => 'boolean',
    ];
}
