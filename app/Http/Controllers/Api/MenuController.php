<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class MenuController extends BaseController
{
    // GET /api/menus
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Menu::query();

            if ($request->filled('search')) {
                $query->where('menu_name', 'like', '%' . $request->search . '%');
            }

            $perPage = $request->integer('per_page', 15);
            $menus = $query->orderBy('id', 'desc')->paginate($perPage);

            return $this->sendPaginated($menus, 'Menus retrieved successfully');
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    // POST /api/menu/createmenu
    public function store(Request $request): JsonResponse
    {
       $request->merge([
            'menu_name'   => trim((string) $request->input('menu_name')),
            'menu_routes' => $request->has('menu_routes')
                ? trim((string) $request->input('menu_routes'))
                : null,
        ]);
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }
        try {
            $menu = Menu::create($validator->validated());
            return $this->sendResponse($menu, 'Menu created successfully', 201);
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    // GET /api/menu/{id}
    public function show(string $id): JsonResponse
    {
        $menu = Menu::find($id);
        if (!$menu) {
            return $this->sendNotFound('Menu not found');
        }
        return $this->sendResponse($menu, 'Menu retrieved successfully');
    }

    // PUT/PATCH /api/menu/{id}
    public function update(Request $request, string $id): JsonResponse
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return $this->sendNotFound('Menu not found');
        }

        $request->merge([
            'menu_name' => trim((string) $request->input('menu_name')),
            'menu_routes' => $request->has('menu_routes')
                ? trim((string) $request->input('menu_routes'))
                : null,
        ]);

        // Update menu_sort only when provided
        if ($request->has('menu_sort')) {
            $request->merge([
                'menu_sort' => $request->input('menu_sort') === ''
                    || $request->input('menu_sort') === null
                        ? 0
                        : (int) $request->input('menu_sort'),
            ]);
        }

        $validator = Validator::make(
            $request->all(),
            $this->rules(true, $id)
        );

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        try {
            $menu->update($validator->validated());

            return $this->sendResponse(
                $menu->fresh(),
                'Menu updated successfully'
            );
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }
    // DELETE /api/menu/{id}
    public function destroy(string $id): JsonResponse
    {
        $menu = Menu::find($id);
        if (!$menu) {
            return $this->sendNotFound('Menu not found');
        }
        try {
            $menu->delete();
            return $this->sendMessage('Menu deleted successfully');
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    private function rules(bool $sometimes = false, ?string $ignoreId = null): array
    {
        $rule = $sometimes ? 'sometimes' : 'required';

        $uniqueRule = Rule::unique('menus', 'menu_name');

        if ($ignoreId) {
            $uniqueRule = $uniqueRule->ignore($ignoreId);
        }

        return [
            'menu_name'          => [$rule, 'string', 'min:1', 'max:300', $uniqueRule],
            'menu_sort'          => [$rule, 'integer', 'min:1'],
            'record_status'      => ['nullable', 'string', 'max:15'],
            'menu_routes'        => ['nullable', 'string', 'max:50'],
            'enterprise'         => ['nullable', 'string', 'max:15'],
            'standard'           => ['nullable', 'string', 'max:15'],
            'express'            => ['nullable', 'string', 'max:15'],
            'project_enterprise' => ['nullable', 'string', 'max:50'],
            'project_standard'   => ['nullable', 'string', 'max:50'],
            'project_express'    => ['nullable', 'string', 'max:50'],
        ];
    }

}