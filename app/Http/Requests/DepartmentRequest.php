<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        // Route parameter is {department} for apiResource, {id} if defined manually
        $id = $this->route('department') ?? $this->route('id');

        return [
            'Department' => [
                'required',
                'string',
                'max:100',
                Rule::unique('departments', 'Department')->ignore($id),
            ],
            'AllowOvertime'   => ['required', 'boolean'],
            'SaturdayHalfDay' => ['required', 'boolean'],
            'PayrollType'     => ['required', 'string', 'max:50'],
            'RecordStatus'    => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'Department.unique' => 'Department already exists!',
        ];
    }
}