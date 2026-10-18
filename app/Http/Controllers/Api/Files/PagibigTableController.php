<?php
// app/Http/Controllers/Api/PagibigTableController.php
namespace App\Http\Controllers\Api\Files;

use App\Http\Controllers\Controller;
use App\Http\Requests\PagibigContributionRequest;
use App\Models\PagibigContribution;
use Illuminate\Http\JsonResponse;

class PagibigTableController extends Controller
{
    // GET pagibig/getPagibigTable
    public function index(): JsonResponse
    {
        return response()->json(
            PagibigContribution::orderBy('SalaryFrom')->get()
        );
    }

    // GET pagibig/getPagibigTable/{id}
    public function show(int $id): JsonResponse
    {
        return response()->json(PagibigContribution::findOrFail($id));
    }

    // GET pagibig/getPagibigBySalary/{salary}
    public function lookup(float $salary): JsonResponse
    {
        $record = PagibigContribution::where('RecordStatus', 'Active')
            ->where('SalaryFrom', '<=', $salary)
            ->where('SalaryTo', '>=', $salary)
            ->firstOrFail();

        return response()->json($record);
    }

    // POST pagibig/savePagibigTable
    public function store(PagibigContributionRequest $request): JsonResponse
    {
        $record = PagibigContribution::create($request->validated());

        return response()->json($record, 201);
    }

    // PUT pagibig/updatePagibigTable/{id}
    public function update(PagibigContributionRequest $request, int $id): JsonResponse
    {
        $record = PagibigContribution::findOrFail($id);
        $record->update($request->validated());

        return response()->json($record);
    }

    // DELETE pagibig/deletePagibigTable/{id}
    public function destroy(int $id): JsonResponse
    {
        PagibigContribution::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}