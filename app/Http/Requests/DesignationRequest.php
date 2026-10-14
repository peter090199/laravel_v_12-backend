<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'Designation' => [
                'required',
                'string',
                'max:100',
                Rule::unique('designations', 'Designation')->ignore($id),
            ],
            'Salary'       => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'RecordStatus' => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'Designation.unique' => 'Designation already exists!',
        ];
    }
}