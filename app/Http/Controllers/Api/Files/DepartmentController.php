<?php

namespace App\Http\Controllers\Api\Files;

use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Http\Requests\DepartmentRequest;
use App\Models\Departments;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class DepartmentController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $departments = Departments::query()
                ->when($request->search, fn ($q, $search) => $q->where(
                    fn ($query) => $query
                        ->where('Department', 'like', "%{$search}%")
                        ->orWhere('PayrollType', 'like', "%{$search}%")
                        ->orWhere('RecordStatus', 'like', "%{$search}%")
                ))
                ->orderBy('id')
                ->paginate($request->integer('per_page', 15));

            return $this->sendPaginated($departments);
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    public function show(string $id): JsonResponse
    {
        $department = Departments::find($id);

        return $department
            ? $this->getResponse($department)
            : $this->sendNotFound('Department not found');
    }

    public function store(DepartmentRequest $request): JsonResponse
    {
        try {
            $department = Departments::create($request->validated());

            return $this->getResponse($department, 'Save successfully', 200);
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    public function update(DepartmentRequest $request, string $id): JsonResponse
    {
        $department = Departments::find($id);

        if (!$department) {
            return $this->sendNotFound('Department not found');
        }

        try {
            $department->update($request->validated());

            return $this->getResponse($department->refresh(), 'Department updated');
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        $department = Departments::find($id);

        if (!$department) {
            return $this->sendNotFound('Department not found');
        }

        try {
            $department->delete();

            return $this->sendMessage('Department deleted');
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }
}