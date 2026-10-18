<?php
// app/Http/Requests/SssTableRequest.php
namespace App\Http\Requests;

use App\Models\SssTable;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SssTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'SalaryFrom' => ['required', 'numeric', 'min:0'],
            'SalaryTo' => [
                'required',
                'numeric',
                'gte:SalaryFrom'
            ],
            'ER' => ['required', 'numeric', 'min:0'],
            'EE' => ['required', 'numeric', 'min:0'],
            'EC' => ['required', 'numeric', 'min:0'],
            'RecordStatus' => ['required', 'in:Active,Inactive'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty() || $this->input('RecordStatus') !== 'Active') {
                return;
            }

            $current = $this->route('sss_table'); // null on create

            $overlap = SssTable::where('RecordStatus', 'Active')
                ->when($current, fn($q) => $q->where('Id', '!=', $current->Id))
                ->where('SalaryFrom', '<=', $this->input('SalaryTo'))
                ->where('SalaryTo', '>=', $this->input('SalaryFrom'))
                ->exists();

            if ($overlap) {
                $v->errors()->add('SalaryFrom', 'This range overlaps an existing active range.');
            }
        });
    }
}
