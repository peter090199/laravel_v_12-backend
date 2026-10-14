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
