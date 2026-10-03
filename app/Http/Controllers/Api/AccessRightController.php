<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Models\AccessRight;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class AccessRightController extends BaseController
{
    // GET /api/access-rights
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $query = AccessRight::query();

        if ($request->filled('search')) {
            $query->where('access_right_name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('record_status')) {
            $query->where('record_status', $request->record_status);
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage);

        return $this->sendPaginated($paginator, 'Access rights retrieved successfully');
    }

    // GET /api/access-rights/{id}
    public function show(int $id): JsonResponse
    {
        $entity = AccessRight::find($id);

        if (!$entity) {
            return $this->sendNotFound("AccessRight with Id {$id} was not found.");
        }

        return $this->getResponse($entity, 'Access right retrieved successfully');
    }

    // POST /api/access-rights
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            // blocks saving if access_right_name already exists in the table
            'access_right_name' => ['required', 'string', 'max:300', 'unique:accessrights,access_right_name'],
            'record_status'     => ['nullable', 'string', 'max:15'],
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        try {
            $entity = AccessRight::create([
                'access_right_name' => trim($request->access_right_name),
                'record_status'     => $request->filled('record_status')
                    ? trim($request->record_status)
                    : 'active',
            ]);

            return $this->sendResponse($entity, 'Record created successfully.', 201);
        } catch (Throwable $e) {
            return $this->sendServerError($e, 'An error occurred while creating the record.');
        }
    }

    // PUT/PATCH /api/access-rights/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $entity = AccessRight::find($id);
        if (!$entity) {
            return $this->sendNotFound("AccessRight with Id {$id} was not found.");
        }
        $validator = Validator::make($request->all(), [
            'access_right_name' => [
                'required', 'string', 'max:300'
            ],
            'record_status' => ['nullable', 'string', 'max:15'],
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        try {
            $entity->update([
                'access_right_name' => trim($request->access_right_name),
                'record_status'     => $request->filled('record_status')
                    ? trim($request->record_status)
                    : $entity->record_status,
            ]);

            return $this->sendResponse($entity->fresh(), 'Record updated successfully.');
        } catch (Throwable $e) {
            return $this->sendServerError($e, 'An error occurred while updating the record.');
        }
    }

    // POST /api/access-rights/save
    // Convenience endpoint: inserts when no id is sent, updates when id is sent
    public function save(Request $request): JsonResponse
    {
        return $request->filled('id')
            ? $this->update($request, (int) $request->id)
            : $this->store($request);
    }

    // DELETE /api/access-rights/{id}
    public function destroy(int $id): JsonResponse
    {
        $entity = AccessRight::find($id);

        if (!$entity) {
            return $this->sendNotFound("AccessRight with Id {$id} was not found.");
        }

        try {
            $entity->delete();
            return $this->sendMessage('Record deleted successfully.');
        } catch (Throwable $e) {
            return $this->sendServerError($e, 'An error occurred while deleting the record.');
        }
    }
}