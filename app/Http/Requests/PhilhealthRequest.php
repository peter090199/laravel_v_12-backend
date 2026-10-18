<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class PhilhealthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'SalaryFrom'   => ['required', 'numeric', 'min:0'],
            'SalaryTo'     => ['required', 'numeric', 'gte:SalaryFrom'],
            'EmployeeRate' => ['required', 'numeric', 'between:0,100'],
            'EmployerRate' => ['required', 'numeric', 'between:0,100'],
            'PayrollType'  => ['required', 'in:Monthly,Semi-Monthly'],
            'RecordStatus' => ['sometimes', 'in:Active,Inactive'],
        ];
    }
}
