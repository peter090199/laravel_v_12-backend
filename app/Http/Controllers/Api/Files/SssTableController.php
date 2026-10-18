<?php

namespace App\Http\Controllers\Api\Files;

use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Http\Requests\SssTableRequest;
use App\Models\SssTable;
use Illuminate\Http\JsonResponse;
use Throwable;

class SssTableController extends BaseController
{
    public function index(): JsonResponse
    {
        try {
            $rows = SssTable::orderBy('SalaryFrom')->get();

            return $this->sendResponse(
                $rows,
                'SSS records retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->sendServerError(
                $e,
                'Failed to retrieve SSS records.'
            );
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $row = SssTable::find($id);

            if (!$row) {
                return $this->sendNotFound('SSS record not found.');
            }

            return $this->sendResponse(
                $row,
                'SSS record retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    /**
     * Check whether an active salary range overlaps another active range.
     * Exclude the current record when updating.
     */
    private function hasOverlappingRange(
        float $salaryFrom,
        float $salaryTo,
        string $status,
        $excludeId = null
    ): bool {
        if ($status !== 'Active') {
            return false;
        }

        $query = SssTable::where('RecordStatus', 'Active')
            ->where('SalaryFrom', '<=', $salaryTo)
            ->where('SalaryTo', '>=', $salaryFrom);

        if ($excludeId !== null) {
            $query->where('Id', '!=', $excludeId);
        }

        return $query->exists();
    }

    private function overlapError(): JsonResponse
    {
        return response()->json([
            'message' => 'This range overlaps an existing active range.',
            'errors' => [
                'SalaryFrom' => [
                    'This range overlaps an existing active range.'
                ]
            ]
        ], 422);
    }

    public function store(SssTableRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($this->hasOverlappingRange(
                (float) $data['SalaryFrom'],
                (float) $data['SalaryTo'],
                $data['RecordStatus']
            )) {
                return $this->overlapError();
            }

            $row = SssTable::create($data);

            return $this->sendResponse(
                $row,
                'SSS record created successfully.',
                201
            );
        } catch (Throwable $e) {
            return $this->sendServerError(
                $e,
                'Failed to create SSS record.'
            );
        }
    }

    public function update(
        SssTableRequest $request,
        $id
    ): JsonResponse {
        try {
            $row = SssTable::find($id);

            if (!$row) {
                return $this->sendNotFound('SSS record not found.');
            }

            $data = $request->validated();

            if ($this->hasOverlappingRange(
                (float) $data['SalaryFrom'],
                (float) $data['SalaryTo'],
                $data['RecordStatus'],
                $row->Id
            )) {
                return $this->overlapError();
            }

            $row->update($data);

            return $this->sendResponse(
                $row->fresh(),
                'SSS record updated successfully.'
            );
        } catch (Throwable $e) {
            return $this->sendServerError(
                $e,
                'Failed to update SSS record.'
            );
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $row = SssTable::find($id);

            if (!$row) {
                return $this->sendNotFound('SSS record not found.');
            }

            $row->delete();

            return $this->sendMessage(
                'SSS record deleted successfully.'
            );
        } catch (Throwable $e) {
            return $this->sendServerError(
                $e,
                'Failed to delete SSS record.'
            );
        }
    }

    public function lookup(float $salary): JsonResponse
    {
        try {
            $row = SssTable::where('RecordStatus', 'Active')
                ->where('SalaryFrom', '<=', $salary)
                ->where('SalaryTo', '>=', $salary)
                ->first();

            if (!$row) {
                return $this->sendNotFound(
                    'No active SSS bracket found for this salary.'
                );
            }

            return $this->sendResponse(
                $row,
                'SSS bracket retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->sendServerError(
                $e,
                'Failed to look up SSS bracket.'
            );
        }
    }
}