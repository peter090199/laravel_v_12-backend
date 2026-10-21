<?php
namespace App\Http\Controllers\Api\Files;
use App\Http\Controllers\Controller;
use App\Models\ConfigFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfigFileController extends Controller
{
    // GET files/config
    public function index(): JsonResponse
    {
        $rows = ConfigFile::where('RecordStatus', '!=', 'Inactive')
            ->get()
            ->map(fn (ConfigFile $c) => $c->toApi());

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function show(string $id): JsonResponse
    {
        $config = ConfigFile::firstOrCreate(
            ['ConfigID' => $id],
            ['RecordStatus' => 'Active']
        );

        return response()->json(['success' => true, 'data' => $config->fresh()->toApi()]);
    }

    // POST files/saveconfig
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(ConfigFile::rules());

        $config = ConfigFile::create(
            ConfigFile::toColumns($data) + ['RecordStatus' => 'Active']
        );

        return response()->json(['success' => true, 'data' => $config->toApi()], 201);
    }

    // PUT files/updateconfigId/{id}
    public function update(Request $request, string $id): JsonResponse
    {
        $config = ConfigFile::findOrFail($id);
        $data = $request->validate(ConfigFile::rules(partial: true));

        $config->update(ConfigFile::toColumns($data));

        return response()->json(['success' => true, 'data' => $config->fresh()->toApi()]);
    }

    // DELETE files/deleteconfigId/{id}  (soft delete via RecordStatus)
    public function destroy(string $id): JsonResponse
    {
        $config = ConfigFile::findOrFail($id);
        $config->update(['RecordStatus' => 'Inactive']);

        return response()->json(['success' => true, 'message' => 'Config deleted.']);
    }
}