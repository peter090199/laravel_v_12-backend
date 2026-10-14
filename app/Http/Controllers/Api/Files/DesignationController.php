<?php

namespace App\Http\Controllers\Api\Files;

use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Http\Requests\DesignationRequest;
use App\Models\Designation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class DesignationController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $designations = Designation::query()
                ->when($request->search, fn ($q, $search) => $q->where(
                    fn ($query) => $query
                        ->where('Designation', 'like', "%{$search}%")
                        ->orWhere('RecordStatus', 'like', "%{$search}%")
                ))
                ->orderBy('id')
                ->paginate($request->integer('per_page', 15));

            return $this->sendPaginated($designations);
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    public function show(string $id): JsonResponse
    {
        $designation = Designation::find($id);

        return $designation
            ? $this->getResponse($designation)
            : $this->sendNotFound('Designation not found');
    }

    public function store(DesignationRequest $request): JsonResponse
    {
        try {
            $designation = Designation::create($request->validated());

            return $this->getResponse($designation, 'Save successfully', 200);
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    public function update(DesignationRequest $request, string $id): JsonResponse
    {
        $designation = Designation::find($id);

        if (!$designation) {
            return $this->sendNotFound('Designation not found');
        }

        try {
            $designation->update($request->validated());

            return $this->getResponse($designation->refresh(), 'Designation updated');
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        $designation = Designation::find($id);

        if (!$designation) {
            return $this->sendNotFound('Designation not found');
        }

        try {
            $designation->delete();

            return $this->sendMessage('Designation deleted');
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }
}