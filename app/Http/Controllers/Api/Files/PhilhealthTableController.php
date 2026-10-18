<?php
namespace App\Http\Controllers\Api\Files;


use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Http\Requests\PhilhealthRequest;
use App\Models\PhilhealthTable;
use Illuminate\Http\JsonResponse;

class PhilhealthTableController extends BaseController
{
    public function index(): JsonResponse
    {
        // The Angular MatTableDataSource handles paging/sorting/filtering client-side
        return response()->json(
            PhilhealthTable::orderBy('PayrollType')->orderBy('SalaryFrom')->get()
        );
    }

    public function store(PhilhealthRequest $request): JsonResponse
    {
        $record = PhilhealthTable::create($request->validated());

        return response()->json($record, 201);
    }

    public function show(PhilhealthTable $pagibigContribution): JsonResponse
    {
        return response()->json($pagibigContribution);
    }

    public function update(PhilhealthRequest $request, PhilhealthTable $pagibigContribution): JsonResponse
    {
        $pagibigContribution->update($request->validated());

        return response()->json($pagibigContribution);
    }

    public function destroy(PhilhealthTable $pagibigContribution): JsonResponse
    {
        $pagibigContribution->delete();

        return response()->json(null, 204);
    }
}