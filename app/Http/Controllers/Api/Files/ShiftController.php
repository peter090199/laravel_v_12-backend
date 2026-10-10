<?php

namespace App\Http\Controllers\Api\Files;

use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Http\Requests\ShiftRequest;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ShiftController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $shifts = Shift::query()
                ->when($request->search, function ($q, $search) {
                    $q->where(
                        fn($query) => $query
                            ->where('DutyShift', 'like', "%{$search}%")
                            ->orWhere('RecordStatus', 'like', "%{$search}%")
                    );
                })
                ->orderBy('id')
                ->paginate($request->integer('per_page', 15));

            return $this->sendPaginated($shifts);
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    public function show(string $id): JsonResponse
    {
        $shift = Shift::find($id);

        return $shift
            ? $this->getResponse($shift)
            : $this->sendNotFound('Shift not found');
    }

    public function store(ShiftRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $exists = Shift::where('DutyFrom', $data['DutyFrom'])
                ->where('DutyTo', $data['DutyTo'])
                ->where('DutyShift', $data['DutyShift'])
                ->exists();

                if ($exists) {
                     return $this->getResponse($exists, "Shifts already exist!", 422);
                } 

            $shift = Shift::create($data);

            return $this->getResponse($shift, "Save successfully", 200);
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }


    /**
     * PUT/PATCH /api/shifts/{id}
     */
    public function update(
        ShiftRequest $request,
        string $id
    ): JsonResponse {
        $shift = Shift::find($id);

        if (!$shift) {
            return $this->sendNotFound('Shift not found');
        }

        try {
            $shift->update($request->validated());

            return $this->getResponse(
                $shift->refresh(),
                'Shift updated'
            );
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    /**
     * DELETE /api/shifts/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $shift = Shift::find($id);

        if (!$shift) {
            return $this->sendNotFound('Shift not found');
        }

        try {
            $shift->delete();

            return $this->sendMessage('Shift deleted');
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }
}
