<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;
use App\Models\AccessRight;
use App\Models\Menu;
use App\Models\SubMenu;
use Illuminate\Support\Facades\DB;


class RolePermissionController extends BaseController
{
    // GET /api/role-permissions
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $query = RolePermission::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('access_right_name', 'like', "%{$search}%")
                  ->orWhere('menu_name', 'like', "%{$search}%")
                  ->orWhere('sub_menu_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('access_right_id')) {
            $query->where('access_right_id', $request->access_right_id);
        }

        if ($request->filled('menu_id')) {
            $query->where('menu_id', $request->menu_id);
        }

        if ($request->filled('sub_menu_id')) {
            $query->where('sub_menu_id', $request->sub_menu_id);
        }

        if ($request->filled('record_status')) {
            $query->where('record_status', $request->record_status);
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage);

        return $this->sendPaginated($paginator, 'Role permissions retrieved successfully');
    }

    // GET /api/role-permissions/{id}
    public function show(int $id): JsonResponse
    {
        $permissions = RolePermission::query()
            ->where('access_right_id', $id)
            ->orderBy('menu_sort')
            ->orderBy('sub_menu_sort')
            ->get();

        if ($permissions->isEmpty()) {
            return $this->sendNotFound(
                "No role permissions found for access right ID {$id}."
            );
        }

        return $this->getResponse(
            $permissions,
            'Role permissions retrieved successfully'
        );
    }
    // POST /api/role-permissions
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }
        $subMenuId = $request->filled('sub_menu_id') ? $request->sub_menu_id : null;
        try {
            $entity = RolePermission::create([
                'access_right_id'   => $request->access_right_id,
                'access_right_name' => trim($request->access_right_name),
                'menu_id'           => $request->menu_id,
                'menu_name'         => trim($request->menu_name),
                'sub_menu_id'       => $subMenuId,
                'sub_menu_name'     => $request->filled('sub_menu_name')
                    ? trim($request->sub_menu_name)
                    : null,
                'record_status'     => $request->filled('record_status')
                    ? trim($request->record_status)
                    : 'active',
            ]);
            return $this->sendResponse( $entity,'Record created successfully.',201);
        } catch (Throwable $e) {
            return $this->sendServerError($e,'An error occurred while creating the record.');
        }
    }

    // PUT/PATCH /api/role-permissions/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $entity = RolePermission::find($id);

        if (!$entity) {
            return $this->sendNotFound("RolePermission with Id {$id} was not found.");
        }
        $validator = Validator::make($request->all(), $this->rules($id));
        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        try {
            $entity->update([
                'access_right_id'   => $request->access_right_id,
                'access_right_name' => trim($request->access_right_name),
                'menu_id'           => $request->menu_id,
                'menu_name'         => trim($request->menu_name),
                'sub_menu_id'       => $request->sub_menu_id,
                'sub_menu_name'     => trim($request->sub_menu_name),
                'record_status'     => $request->filled('record_status')
                    ? trim($request->record_status)
                    : $entity->record_status,
            ]);

            return $this->sendResponse($entity->fresh(), 'Record updated successfully.');
        } catch (Throwable $e) {
            return $this->sendServerError($e, 'An error occurred while updating the record.');
        }
    }

    // POST /api/role-permissions/save
    // Convenience endpoint: inserts when no id is sent, updates when id is sent
    public function save(Request $request): JsonResponse
    {
        return $request->filled('id')
            ? $this->update($request, (int) $request->id)
            : $this->store($request);
    }

    // DELETE /api/role-permissions/{id}
    public function destroy(int $id): JsonResponse
    {
        $entity = RolePermission::find($id);

        if (!$entity) {
            return $this->sendNotFound("RolePermission with Id {$id} was not found.");
        }

        try {
            $entity->delete();
            return $this->sendMessage('Record deleted successfully.');
        } catch (Throwable $e) {
            return $this->sendServerError($e, 'An error occurred while deleting the record.');
        }
    }

    /**
     * Shared validation rules for store/update.
     * Blocks duplicate saves of the SAME access_right + menu + sub_menu combination.
     */
    protected function rules(): array
    {
        return [
            'access_right_id'   => 'required',
            'access_right_name' => 'required|string',
            'menu_id'            => 'required',
            'menu_name'          => 'required|string',
            'sub_menu_id'        => 'nullable',
            'sub_menu_name'      => 'nullable|string',
            'record_status'      => 'nullable|string',
        ];
    }


    public function sync(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'access_right_id' => [
                'required',
                'integer',
                'exists:' . (new AccessRight)->getTable() . ',id',
            ],

            'permissions' => [
                'present',
                'array',
            ],

            'permissions.*.menu_id' => [
                'required',
                'integer',
                'exists:' . (new Menu)->getTable() . ',id',
            ],

            'permissions.*.sub_menu_id' => [
                'nullable',
                'integer',
                'exists:' . (new SubMenu)->getTable() . ',id',
            ],

            'permissions.*.menu_sort' => [
                'nullable',
                'integer',
            ],

            'permissions.*.sub_menu_sort' => [
                'nullable',
                'integer',
            ],

            'permissions.*.record_status' => [
                'required',
                'string',
            ],
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        try {
            $data = $validator->validated();

            $accessRight = AccessRight::findOrFail(
                $data['access_right_id']
            );

            DB::transaction(function () use ($data, $accessRight) {

                // Remove current permissions
                RolePermission::where(
                    'access_right_id',
                    $accessRight->id
                )->delete();

                foreach ($data['permissions'] as $permission) {

                    $menu = Menu::findOrFail(
                        $permission['menu_id']
                    );

                    $subMenu = null;

                    if (!empty($permission['sub_menu_id'])) {
                        $subMenu = SubMenu::findOrFail(
                            $permission['sub_menu_id']
                        );
                    }

                    RolePermission::create([
                        'access_right_id' => $accessRight->id,
                        'access_right_name' => $accessRight->access_right_name,

                        'menu_id' => $menu->id,
                        'menu_name' => $menu->menu_name,
                        'menu_sort' => $permission['menu_sort'] ?? null,

                        'sub_menu_id' => $subMenu?->id,
                        'sub_menu_name' => $subMenu?->sub_menu_name,
                        'sub_menu_sort' => $permission['sub_menu_sort'] ?? null,

                        'record_status' => $permission['record_status'] ?? 'active',
                    ]);
                }
            });

            return $this->sendResponse(
                null,
                'Access updated successfully.'
            );

        } catch (Throwable $e) {

            return $this->sendServerError($e);
        }
    }

    // public function sync(Request $request): JsonResponse
    // {
    //     $validator = Validator::make($request->all(), [
    //         'access_right_id'                 => ['required', 'integer', 'exists:access_rights,id'],
    //         'permissions'                     => ['present', 'array'],
    //         'permissions.*.menu_id'           => ['required', 'integer', 'exists:menus,id'],
    //         'permissions.*.sub_menu_id'       => ['nullable', 'integer', 'exists:submenus,id'],
    //         'permissions.*.record_status'     => ['required'],
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->sendValidationError($validator->errors());
    //     }

    //     try {
    //         $data = $validator->validated();
    //         $accessRight = AccessRight::findOrFail($data['access_right_id']);

    //         DB::transaction(function () use ($request, $accessRight) {
    //             RolePermission::where('access_right_id', $accessRight->id)->delete();

    //             foreach ($request->input('permissions') as $p) {
    //                 $menu = Menu::findOrFail($p['menu_id']);
    //                 $sub  = !empty($p['sub_menu_id']) ? SubMenu::findOrFail($p['sub_menu_id']) : null;

    //                 RolePermission::create([
    //                     'access_right_id'   => $accessRight->id,
    //                     'access_right_name' => $accessRight->access_right_name,
    //                     'menu_id'           => $menu->id,
    //                     'menu_name'         => $menu->menu_name,
    //                     'menu_sort'         => $p['menu_sort'] ?? null,
    //                     'sub_menu_id'       => $sub?->id,
    //                     'sub_menu_name'     => $sub?->sub_menu_name,
    //                     'sub_menu_sort'     => $p['sub_menu_sort'] ?? null,
    //                     'record_status'     => 'active',
    //                 ]);
    //             }
    //         });

    //         return $this->sendResponse(null, 'Access updated successfully');
    //     } catch (Throwable $e) {
    //         return $this->sendServerError($e);
    //     }
    // }

}