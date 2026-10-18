<?php
// app/Http/Requests/PagibigContributionRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PagibigContributionRequest extends FormRequest
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
            'EE'           => ['required', 'numeric', 'min:0'],
            'ER'           => ['required', 'numeric', 'min:0'],
            'RecordStatus' => ['sometimes', 'in:Active,Inactive'],
        ];
    }
}