<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Models\Menu;
use App\Models\SubMenu;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class SubMenuController extends BaseController
{
   // GET /api/submenus
    public function index(Request $request): JsonResponse
    {
        try {
            $query = SubMenu::query();

            if ($request->filled('search')) {
                $query->where('sub_menu_name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('menu_id')) {
                $query->where('menu_id', $request->menu_id);
            }

            $perPage = $request->integer('per_page', 15);

            $subMenus = $query
                ->orderBy('sub_menu_sort', 'asc')
                ->orderBy('id', 'asc')
                ->paginate($perPage);

            return $this->sendPaginated($subMenus, 'Submenus retrieved successfully');
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    // POST /api/submenu/createsubmenu
    public function store(Request $request): JsonResponse
    {
        $this->trimInput($request);

        $validator = Validator::make($request->all(), [
            'menu_id'         => ['required', 'integer', 'exists:menus,id'],
            'sub_menu_name'   => [
                'required', 'string', 'max:255',
                Rule::unique('submenus', 'sub_menu_name')
                    ->where(fn ($q) => $q->where('menu_id', $request->input('menu_id'))),
            ],
            'sub_menu_routes' => ['nullable', 'string', 'max:255'],
            'icon'            => ['nullable', 'string', 'max:100'],
            'sub_menu_sort'   => ['nullable', 'integer', 'min:0'],
            'record_status'   => ['sometimes', 'required'],
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        try {
            $data = $validator->validated();

            // menu_name is stored on the submenu, so copy it from the parent menu
            $data['menu_name'] = Menu::findOrFail($data['menu_id'])->menu_name;
            $data['record_status'] = $data['record_status'] ?? 1;

            // No sort sent: place it last within its parent menu
            if (!isset($data['sub_menu_sort'])) {
                $data['sub_menu_sort'] = (int) SubMenu::where('menu_id', $data['menu_id'])
                    ->max('sub_menu_sort') + 1;
            }

            $subMenu = SubMenu::create($data);

            return $this->sendResponse($subMenu, 'Submenu created successfully', 201);
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    // GET /api/submenu/{id}
    public function show(string $id): JsonResponse
    {
        $subMenu = SubMenu::find($id);
        if (!$subMenu) {
            return $this->sendNotFound('Submenu not found');
        }
        return $this->getResponse($subMenu, 'Submenu retrieved successfully');
    }

    // PUT/PATCH /api/submenu/{id}
    public function updateBySubmenuId(Request $request, int $id): JsonResponse
    {
        $subMenu = SubMenu::find($id);

        if (!$subMenu) {
            return $this->sendNotFound('Submenu not found');
        }

        $this->trimInput($request);

        // Use the new menu_id if sent, otherwise keep the current one
        $menuId = $request->input('menu_id', $subMenu->menu_id);

        $validated = $request->validate([
            'menu_id'         => ['sometimes', 'required', 'integer', 'exists:menus,id'],
            'sub_menu_name'   => [
                'sometimes', 'required', 'string', 'max:255',
                Rule::unique('submenus', 'sub_menu_name')
                    ->where(fn ($q) => $q->where('menu_id', $menuId))
                    ->ignore($subMenu->id),
            ],
            'sub_menu_routes' => ['sometimes', 'nullable', 'string', 'max:255'],
            'icon'            => ['sometimes', 'nullable', 'string', 'max:100'],
            'sub_menu_sort'   => ['sometimes', 'required', 'integer', 'min:0'],
            'record_status'   => ['sometimes', 'required'],
        ]);

        try {
            // Keep menu_name in sync with the parent menu
            if (isset($validated['menu_id'])) {
                $validated['menu_name'] = Menu::findOrFail($validated['menu_id'])->menu_name;
            }

            $subMenu->update($validated);

            return $this->sendResponse($subMenu->fresh(), 'Submenu updated successfully');
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    // DELETE /api/submenu/{id}
    public function destroy(string $id): JsonResponse
    {
        $subMenu = SubMenu::find($id);
        if (!$subMenu) {
            return $this->sendNotFound('Submenu not found');
        }

        try {
            $subMenu->delete();
            return $this->sendMessage('Submenu deleted successfully');
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    /**
     * Trim only the string fields that were actually sent.
     */
    private function trimInput(Request $request): void
    {
        $input = $request->only(['sub_menu_name', 'sub_menu_routes', 'icon']);

        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $input[$key] = trim($value);
            }
        }

        $request->merge($input);
    }
}