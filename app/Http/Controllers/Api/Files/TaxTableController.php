<?php

namespace App\Http\Controllers\Api\Files;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaxTableRequest;
use App\Models\TaxTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxTableController extends Controller
{
    // GET tax/getTaxTable
    public function index(): JsonResponse
    {
        return response()->json(
            TaxTable::orderBy('PayrollType')->orderBy('SalaryFrom')->get()
        );
    }

    // GET tax/getTaxTable/{id}
    public function show(int $id): JsonResponse
    {
        return response()->json(TaxTable::findOrFail($id));
    }

    // GET tax/getTaxBySalary/{salary}?payrollType=Monthly
    public function lookup(Request $request, float $salary): JsonResponse
    {
        $type = $request->query('payrollType', 'Monthly');

        $record = TaxTable::where('RecordStatus', 'Active')
            ->where('PayrollType', $type)
            ->where('SalaryFrom', '<=', $salary)
            ->where('SalaryTo', '>=', $salary)
            ->firstOrFail();

        return response()->json($record);
    }

    // POST tax/saveTaxTable
    public function store(TaxTableRequest $request): JsonResponse
    {
        $record = TaxTable::create($request->validated());

        return response()->json($record, 201);
    }

    // PUT tax/updateTaxTable/{id}
    public function update(TaxTableRequest $request, int $id): JsonResponse
    {
        $record = TaxTable::findOrFail($id);
        $record->update($request->validated());

        return response()->json($record);
    }

    // DELETE tax/deleteTaxTable/{id}
    public function destroy(int $id): JsonResponse
    {
        TaxTable::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}