<?php

namespace App\Http\Controllers\Api\Files;

use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Http\Controllers\Api\SharedRoutine\ControlNumberRoutine;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class EmployeeController extends BaseController
{
    private const CONTROL_CODE    = 'EMPLOYEE';
    private const CONTROL_PREFIX  = 'EMP';
    private const CONTROL_PADDING = 5; // EMP-00001

    public function __construct(private ControlNumberRoutine $controlNumbers) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $employees = Employee::query()
                ->when($request->search, fn($q, $search) => $q->where(
                    fn($query) => $query
                        ->where('EmpID', 'like', "%{$search}%")
                        ->orWhere('BiometricID', 'like', "%{$search}%")
                        ->orWhere('EmpName', 'like', "%{$search}%")
                        ->orWhere('Department', 'like', "%{$search}%")
                        ->orWhere('Designation', 'like', "%{$search}%")
                        ->orWhere('RecordStatus', 'like', "%{$search}%")
                ))
                ->when($request->department, fn($q, $v) => $q->where('Department', $v))
                ->when($request->status, fn($q, $v) => $q->where('RecordStatus', $v))
                ->orderBy('id')
                ->paginate($request->integer('per_page', 15));

            return $this->sendPaginated($employees);
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    public function show(string $id): JsonResponse
    {
        $employee = Employee::find($id);

        return $employee
            ? $this->getResponse($employee)
            : $this->sendNotFound('Employee not found');
    }

    public function getByEmpID(string $empID): JsonResponse
    {
        $employee = Employee::where('EmpID', $empID)->first();

        return $employee
            ? $this->getResponse($employee)
            : $this->sendNotFound('Employee not found');
    }


    public function updateEmployeeByEmpID(Request $request, string $empID): JsonResponse
    {
        $employee = Employee::where('EmpID', $empID)->first();

        if (!$employee) {
            return $this->sendNotFound('Employee not found');
        }

        $validated = $request->validate([
            // Personal Information
            'EmpFirstName'          => 'sometimes|nullable|string|max:100',
            'EmpMiddleName'         => 'sometimes|nullable|string|max:100',
            'EmpLastName'           => 'sometimes|nullable|string|max:100',
            'Gender'                => 'sometimes|nullable|string|max:30',
            'CivilStatus'           => 'sometimes|nullable|string|max:50',
            'Religion'              => 'sometimes|nullable|string|max:100',
            'MotherMaidenFullName'  => 'sometimes|nullable|string|max:200',
            'FatherFullName'        => 'sometimes|nullable|string|max:200',
            'HighestEducationalAtt' => 'sometimes|nullable|string|max:150',
            'NameOfSchool'          => 'sometimes|nullable|string|max:200',
            'DegreeCourse'          => 'sometimes|nullable|string|max:150',
            'HighestLevel'          => 'sometimes|nullable|string|max:100',

            // Contact Information
            'Address'               => 'sometimes|nullable|string|max:500',
            'ContactNo'             => 'sometimes|nullable|string|max:30',
            'EmailAdd'              => 'sometimes|nullable|email|max:150',
            'PermanentAddress'      => 'sometimes|nullable|string|max:500',
            'PresentAddress'        => 'sometimes|nullable|string|max:500',
            'ContactPerson'         => 'sometimes|nullable|string|max:150',
            'ContactPersonNo'       => 'sometimes|nullable|string|max:30',

            // Employment Information
            'BiometricID'           => 'sometimes|nullable|string|max:50',
            'DateEmployed'          => 'sometimes|nullable|date',
            'ProjectId'             => 'sometimes|nullable',
            'ProjectName'           => 'sometimes|nullable|string|max:150',

            // Government Information
            'InsuranceNo'           => 'sometimes|nullable|string|max:50',
            'PagibigNo'             => 'sometimes|nullable|string|max:50',
            'PhilHealthNo'          => 'sometimes|nullable|string|max:50',
            'SSSNo'                 => 'sometimes|nullable|string|max:50',
            'TIN'                   => 'sometimes|nullable|string|max:50',
        ]);

        if (empty($validated)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid fields were provided for updating.',
            ], 422);
        }

        $employee->fill($validated);
        $employee->save();

        return $this->sendResponse($employee->fresh(),'Employees updated.');
    }
    
    public function updateEmployeeByEmpIDContibutions(
        Request $request,
        string $empID
    ): JsonResponse {
        $employee = Employee::where('EmpID', $empID)->first();

        if (!$employee) {
            return $this->sendNotFound('Employee not found.');
        }

        $validated = $request->validate([
            'empId' => [
                'required',
                'string',
                Rule::in([$empID]),
            ],

            'sss' => ['sometimes', 'array'],
            'sss.enabled' => ['required_with:sss', 'boolean'],
            'sss.ee' => ['required_with:sss', 'numeric', 'min:0'],
            'sss.er' => ['nullable', 'numeric', 'min:0'],
            'sss.frequency' => [
                'nullable', 'integer', 'min:0', 'max:4'
            ],

            'philHealth' => ['sometimes', 'array'],
            'philHealth.enabled' => [
                'required_with:philHealth', 'boolean'
            ],
            'philHealth.ee' => [
                'required_with:philHealth', 'numeric', 'min:0'
            ],
            'philHealth.er' => ['nullable', 'numeric', 'min:0'],
            'philHealth.frequency' => [
                'nullable', 'integer', 'min:0', 'max:4'
            ],

            'pagibig' => ['sometimes', 'array'],
            'pagibig.enabled' => [
                'required_with:pagibig', 'boolean'
            ],
            'pagibig.ee' => [
                'required_with:pagibig', 'numeric', 'min:0'
            ],
            'pagibig.er' => ['nullable', 'numeric', 'min:0'],
            'pagibig.frequency' => [
                'nullable', 'integer', 'min:0', 'max:4'
            ],

            'tax' => ['sometimes', 'array'],
            'tax.enabled' => ['required_with:tax', 'boolean'],
            'tax.ee' => ['required_with:tax', 'numeric', 'min:0'],
            'tax.frequency' => [
                'nullable', 'integer', 'min:0', 'max:4'
            ],

            'insurance' => ['sometimes', 'array'],
            'insurance.enabled' => [
                'required_with:insurance', 'boolean'
            ],
            'insurance.ee' => [
                'required_with:insurance', 'numeric', 'min:0'
            ],
            'insurance.frequency' => [
                'nullable', 'integer', 'min:0', 'max:4'
            ],
        ]);

        $mapping = [
            'sss' => [
                'amount' => 'SSS',
                'employer' => 'SSSER',
                'frequency' => 'SSSFId',
            ],
            'philHealth' => [
                'amount' => 'PhilHealth',
                'employer' => 'PhilHealthER',
                'frequency' => 'PhilFId',
            ],
            'pagibig' => [
                'amount' => 'Pagibig',
                'employer' => 'PagibigER',
                'frequency' => 'PagibigFId',
            ],
            'tax' => [
                'amount' => 'Tax',
                'frequency' => 'TaxFId',
            ],
            'insurance' => [
                'amount' => 'Insurance',
                'frequency' => 'InsFId',
            ],
        ];

        $updates = [];

        foreach ($mapping as $key => $columns) {
            if (!array_key_exists($key, $validated)) {
                continue;
            }

            $item = $validated[$key];
            $enabled = (bool) $item['enabled'];

            // Employee contribution
            $updates[$columns['amount']] = $enabled
                ? (float) $item['ee']
                : 0;

            // Employer contribution, if applicable
            if (isset($columns['employer'])) {
                $updates[$columns['employer']] = $enabled
                    ? (float) ($item['er'] ?? 0)
                    : 0;
            }

            // Zero means no frequency selected
            $updates[$columns['frequency']] = $enabled
                ? (int) ($item['frequency'] ?? 0)
                : 0;
        }

        DB::transaction(function () use ($employee, $updates) {
            $employee->fill($updates);
            $employee->save();
        });

        $employee->refresh();

        return $this->sendResponse(
            $employee,
            'Employee contributions updated successfully.'
        );
    }


    public function getEmployeeByEmpIDContibutions(
    string $empID
): JsonResponse {
    $employee = Employee::where('EmpID', $empID)->first();

    if (!$employee) {
        return $this->sendNotFound('Employee not found.');
    }

    return $this->sendResponse([
        'empId' => $employee->EmpID,

        'sss' => [
            'enabled' => (float) $employee->SSS > 0,
            'ee' => (float) ($employee->SSS ?? 0),
            'er' => (float) ($employee->SSSER ?? 0),
            'frequency' => $employee->SSSFId,
        ],

        'philHealth' => [
            'enabled' => (float) $employee->PhilHealth > 0,
            'ee' => (float) ($employee->PhilHealth ?? 0),
            'er' => (float) ($employee->PhilHealthER ?? 0),
            'frequency' => $employee->PhilFId,
        ],

        'pagibig' => [
            'enabled' => (float) $employee->Pagibig > 0,
            'ee' => (float) ($employee->Pagibig ?? 0),
            'er' => (float) ($employee->PagibigER ?? 0),
            'frequency' => $employee->PagibigFId,
        ],

        'tax' => [
            'enabled' => (float) $employee->Tax > 0,
            'ee' => (float) ($employee->Tax ?? 0),
            'frequency' => $employee->TaxFId,
        ],

        'insurance' => [
            'enabled' => (float) $employee->Insurance > 0,
            'ee' => (float) ($employee->Insurance ?? 0),
            'frequency' => $employee->InsFId,
        ],
    ], 'Employee deductions retrieved successfully.');
}


    /** Preview only. The number is reserved when the employee is saved. */
    public function nextEmpId(): JsonResponse
    {
        try {
            return $this->getResponse([
                'EmpID' => $this->controlNumbers->preview(
                    self::CONTROL_CODE,
                    self::CONTROL_PREFIX,
                    self::CONTROL_PADDING
                ),
            ]);
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    public function store(EmployeeRequest $request): JsonResponse
    {
        $photoPath = null;

        try {
            $data = $request->validated();

            if ($request->hasFile('Photo')) {
                $photoPath = $request->file('Photo')->store('employees', 'public');
                $data['Photo'] = $photoPath;
            }

            $data['InputBy']       = $request->user()?->name ?? $request->user()?->id;
            $data['InputDateTime'] = now();
            $data['MachineName']   = $request->ip();

            // Counter and insert succeed or fail together
            $employee = DB::transaction(function () use ($data) {
                $data['EmpID'] = $this->controlNumbers->next(
                    self::CONTROL_CODE,
                    self::CONTROL_PREFIX,
                    self::CONTROL_PADDING
                );

                return Employee::create($data);
            });

            return $this->sendResponse($data, 'Saved successfully');
        } catch (Throwable $e) {
            $this->deletePhoto($photoPath);

            return $this->sendServerError($e);
        }
    }

    public function update(EmployeeRequest $request, string $id): JsonResponse
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return $this->sendNotFound('Employee not found');
        }

        try {
            $data = $request->validated();

            // Blank password means keep the current one
            foreach (['Password', 'TimecardPassword'] as $field) {
                if (empty($data[$field])) {
                    unset($data[$field]);
                }
            }

            if ($request->hasFile('Photo')) {
                $this->deletePhoto($employee->Photo);
                $data['Photo'] = $request->file('Photo')->store('employees', 'public');
            } else {
                unset($data['Photo']);
            }

            $employee->update($data);

            return $this->getResponse($employee->refresh(), 'Employee updated');
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return $this->sendNotFound('Employee not found');
        }

        try {
            $this->deletePhoto($employee->Photo);
            $employee->delete();

            return $this->sendMessage('Employee deleted');
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    private function deletePhoto(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
