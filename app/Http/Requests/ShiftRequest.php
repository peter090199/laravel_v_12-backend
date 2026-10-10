<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Set default values for boolean and numeric fields.
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        foreach ([
            'IsNightShift',
            'IsNextDayOut',
            'HasBreakShift',
            'HasLunchShift',
        ] as $key) {
            $data[$key] = $this->boolean($key);
        }

        foreach ([
            'LateAfter',
            'OvertimeAfter',
            'FlexBreakMins',
            'FlexLunchMins',
        ] as $key) {
            $data[$key] = $this->input($key) ?? 0;
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        $time = ['date_format:H:i'];

        $requiredIfEnabled = fn (string $flag) =>
            Rule::requiredIf(fn () => $this->boolean($flag));

        return [
            'RecordStatus' => [
                'nullable',
                Rule::in(['Active', 'Inactive']),
            ],

            'DutyHours' => [
                'nullable',
                'numeric',
                'between:0,24',
            ],

            'LateAfter' => [
                'required',
                'integer',
                'min:0',
            ],

            'OvertimeAfter' => [
                'required',
                'integer',
                'min:0',
            ],

            'DutyFrom' => [
                'required',
                ...$time,
            ],

            'DutyTo' => [
                'required',
                ...$time,
            ],

            'DutyShift' => [
                'nullable',
                'string',
                'max:50',
            ],

            'IsNightShift' => ['required', 'boolean'],
            'IsNextDayOut' => ['required', 'boolean'],

            'HasBreakShift' => ['required', 'boolean'],

            'BreakTimeout' => [
                'nullable',
                $requiredIfEnabled('HasBreakShift'),
                ...$time,
            ],

            'BreakTimein' => [
                'nullable',
                $requiredIfEnabled('HasBreakShift'),
                ...$time,
            ],

            'BreakShift' => [
                'nullable',
                'string',
                'max:50',
            ],

            'FlexBreakMins' => [
                'required',
                'integer',
                'min:0',
            ],

            'HasLunchShift' => ['required', 'boolean'],

            'LunchTimeout' => [
                'nullable',
                $requiredIfEnabled('HasLunchShift'),
                ...$time,
            ],

            'LunchTimein' => [
                'nullable',
                $requiredIfEnabled('HasLunchShift'),
                ...$time,
            ],

            'LunchShift' => [
                'nullable',
                'string',
                'max:50',
            ],

            'FlexLunchMins' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }
}
