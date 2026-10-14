<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';

    public $timestamps = false;

    protected $fillable = [
        'EmpID', 'BiometricID', 'EmpName', 'Address', 'ContactNo', 'Department', 'Designation',
        'Tax', 'SSS', 'Pagibig', 'PhilHealth', 'Insurance',
        'TIN', 'SSSNo', 'PhilHealthNo', 'PagibigNo', 'InsuranceNo',
        'Salary', 'StartPayroll', 'RecordStatus', 'Photo',
        'ShiftId', 'Shift', 'LateAfter', 'OvertimeAfter', 'DutyHours',
        'HasBreakShift', 'BreakShift', 'HasLunchShift', 'LunchShift',
        'IsNightShift', 'FlexLunchMins', 'FlexBreakMins', 'IsNextDayOut',
        'Password', 'TimecardPassword', 'BirthDate',
        'ContactPerson', 'ContactPersonNo', 'EmailAdd', 'SalaryType',
        'SSSFId', 'TaxFId', 'PagibigFId', 'PhilFId', 'InsFId',
        'Allowance', 'AllowanceType', 'AllowOvertime', 'SaturdayHalfDay',
        'DateEmployed', 'PayrollType', 'DayOff', 'DayOff2',
        'ProjectId', 'ProjectName',
        'EmpFirstName', 'EmpMiddleName', 'EmpLastName',
        'Gender', 'Religion', 'HighestEducationalAtt', 'NameOfSchool', 'DegreeCourse',
        'HighestLevel', 'CivilStatus', 'MotherMaidenFullName', 'FatherFullName',
        'PermanentAddress', 'PresentAddress', 'BankAccountNo',
        'SSSER', 'PhilHealthER', 'PagibigER', 'EmpStatus',
        'InputBy', 'InputDateTime', 'MachineName',
        'IsDTRInput', 'DTRStatus', 'ScheduleType', 'SalaryDate', 'SalaryStatus',
    ];

    protected $hidden = ['Password', 'TimecardPassword'];

    protected $casts = [
        'Tax'              => 'boolean',
        'SSS'              => 'boolean',
        'Pagibig'          => 'boolean',
        'PhilHealth'       => 'boolean',
        'Insurance'        => 'boolean',
        'HasBreakShift'    => 'boolean',
        'HasLunchShift'    => 'boolean',
        'IsNightShift'     => 'boolean',
        'IsNextDayOut'     => 'boolean',
        'AllowOvertime'    => 'boolean',
        'SaturdayHalfDay'  => 'boolean',
        'IsDTRInput'       => 'boolean',
        'Salary'           => 'decimal:2',
        'Allowance'        => 'decimal:2',
        'SSSER'            => 'decimal:2',
        'PhilHealthER'     => 'decimal:2',
        'PagibigER'        => 'decimal:2',
        'Password'         => 'hashed',
        'TimecardPassword' => 'hashed',
    ];

    protected $appends = ['PhotoUrl'];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->Photo ? asset('storage/' . $this->Photo) : null;
    }
}