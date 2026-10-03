<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Models\Menu;
use App\Models\RolePermission;
use App\Models\SubMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class MenuAccessController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->access_right_id) {
            return $this->getResponse([], 'This account has no access right assigned.');
        }
        return $this->buildMenuTreeForAccessRight((int) $user->access_right_id,'Menu access retrieved successfully');
    }

    public function byAccessRight(int $accessRightId): JsonResponse
    {
        return $this->buildMenuTreeForAccessRight(
            $accessRightId,
            'Menu access retrieved successfully'
        );
    }

    // private function buildMenuTreeForAccessRight(int $accessRightId, string $message): JsonResponse
    // {
    //     try {
    //         $rows = RolePermission::where('access_right_id', $accessRightId)
    //             ->where('record_status', 'active')
    //             ->orderBy('menu_sort')->orderBy('menu_name')
    //             ->orderBy('sub_menu_sort')->orderBy('sub_menu_name')
    //             ->get();

    //         // Fetch all routes in 2 queries (no N+1)
    //         $menuRoutes = Menu::whereIn('id', $rows->pluck('menu_id')->filter()->unique())
    //             ->pluck('menu_routes', 'id');

    //         // FIXED: column is menu_routes in the submenus table, not sub_menu_routes
    //         $subMenuRoutes = SubMenu::whereIn('id', $rows->pluck('sub_menu_id')->filter()->unique())
    //             ->pluck('sub_menu_routes', 'id');

    //         $menus = [];
    //         foreach ($rows as $row) {
    //             $menus[$row->menu_id] ??= [
    //                 'menu_id'     => $row->menu_id,
    //                 'menu_name'   => $row->menu_name,
    //                 'menu_routes' => $menuRoutes[$row->menu_id] ?? null,
    //                 'menu_sort'   => $row->menu_sort,
    //                 'submenus'    => [],
    //             ];

    //             if (!empty($row->sub_menu_id)) {
    //                 $menus[$row->menu_id]['submenus'][] = [
    //                     'sub_menu_id'     => $row->sub_menu_id,
    //                     'sub_menu_name'   => $row->sub_menu_name,
    //                     'sub_menu_routes' => $subMenuRoutes[$row->sub_menu_id] ?? null,
    //                     'sub_menu_sort'   => $row->sub_menu_sort,
    //                 ];
    //             }
    //         }

    //         return $this->getResponse(array_values($menus), $message);
    //     } catch (Throwable $e) {
    //         return $this->sendServerError($e, 'An error occurred while retrieving menu access.');
    //     }
    // }

    private function buildMenuTreeForAccessRight(int $accessRightId, string $message): JsonResponse
    {
        try {
            $rows = RolePermission::where('access_right_id', $accessRightId)
                ->where('record_status', 'active')
                ->get();

            $menuIds    = $rows->pluck('menu_id')->filter()->unique();
            $subMenuIds = $rows->pluck('sub_menu_id')->filter()->unique();

            // Menus (with menu_sort)
            $menusData = Menu::whereIn('id', $menuIds)
                ->get(['id', 'menu_name', 'menu_routes', 'menu_sort'])
                ->keyBy('id');

            // Submenus: name, route and sort come from the submenus table
            $subMenusData = SubMenu::whereIn('id', $subMenuIds)
                ->get(['id', 'sub_menu_name', 'sub_menu_routes', 'sub_menu_sort'])
                ->keyBy('id');

            $menus = [];

            foreach ($rows as $row) {
                $menu = $menusData->get($row->menu_id);

                if (!$menu) {
                    continue;
                }

                if (!isset($menus[$row->menu_id])) {
                    $menus[$row->menu_id] = [
                        'menu_id'     => $menu->id,
                        'menu_name'   => $menu->menu_name,
                        'menu_routes' => $menu->menu_routes,
                        'menu_sort'   => $menu->menu_sort,
                        'submenus'    => [],
                    ];
                }

                if (empty($row->sub_menu_id)) {
                    continue;
                }

                $subMenu = $subMenusData->get($row->sub_menu_id);

                // Skip submenus that no longer exist
                if (!$subMenu) {
                    continue;
                }

                // Keyed by sub_menu_id so duplicate permission rows don't repeat a submenu
                $menus[$row->menu_id]['submenus'][$subMenu->id] = [
                    'sub_menu_id'     => $subMenu->id,
                    'sub_menu_name'   => $subMenu->sub_menu_name,
                    'sub_menu_routes' => $subMenu->sub_menu_routes,
                    'sub_menu_sort'   => $subMenu->sub_menu_sort,
                ];
            }

            // Sort submenus by sub_menu_sort (nulls last), then name, then id
            foreach ($menus as &$menu) {
                $menu['submenus'] = array_values($menu['submenus']);

                usort($menu['submenus'], fn ($a, $b) =>
                    [
                        $a['sub_menu_sort'] ?? PHP_INT_MAX,
                        (string) $a['sub_menu_name'],
                        $a['sub_menu_id'],
                    ]
                    <=>
                    [
                        $b['sub_menu_sort'] ?? PHP_INT_MAX,
                        (string) $b['sub_menu_name'],
                        $b['sub_menu_id'],
                    ]
                );
            }
            unset($menu);

            // Sort menus by menu_sort (nulls last), then name, then id
            $menus = array_values($menus);

            usort($menus, fn ($a, $b) =>
                [
                    $a['menu_sort'] ?? PHP_INT_MAX,
                    (string) $a['menu_name'],
                    $a['menu_id'],
                ]
                <=>
                [
                    $b['menu_sort'] ?? PHP_INT_MAX,
                    (string) $b['menu_name'],
                    $b['menu_id'],
                ]
            );

            return $this->getResponse($menus, $message);

        } catch (Throwable $e) {
            return $this->sendServerError(
                $e,
                'An error occurred while retrieving menu access.'
            );
        }
    }

    // private function buildMenuTreeForAccessRightOld(int $accessRightId, string $message): JsonResponse
    // {
    //     try {
    //         $rows = RolePermission::where('access_right_id', $accessRightId)
    //             ->where('record_status', 'active')
    //             // Rows with no sort go last (MySQL sorts NULL first by default).
    //             ->orderByRaw('menu_sort IS NULL')
    //             ->orderBy('menu_sort')
    //             ->orderBy('menu_name')
    //             ->orderBy('menu_id')              // keeps each menu's rows together on ties
    //             ->orderByRaw('sub_menu_sort IS NULL')
    //             ->orderBy('sub_menu_sort')
    //             ->orderBy('sub_menu_name')
    //             ->orderBy('sub_menu_id')
    //             ->get();

    //         // Fetch all routes in 2 queries (no N+1)
    //         $menuRoutes = Menu::whereIn('id', $rows->pluck('menu_id')->filter()->unique())
    //             ->pluck('menu_routes', 'id');

    //         $subMenuRoutes = SubMenu::whereIn('id', $rows->pluck('sub_menu_id')->filter()->unique())
    //             ->pluck('sub_menu_routes', 'id');

    //         $menus = [];
    //         foreach ($rows as $row) {
    //             $menus[$row->menu_id] ??= [
    //                 'menu_id'     => $row->menu_id,
    //                 'menu_name'   => $row->menu_name,
    //                 'menu_routes' => $menuRoutes[$row->menu_id] ?? null,
    //                 'menu_sort'   => $row->menu_sort,
    //                 'submenus'    => [],
    //             ];

    //             // If older rows of the same menu disagree on menu_sort, use the lowest.
    //             if ($row->menu_sort !== null &&
    //                 ($menus[$row->menu_id]['menu_sort'] === null ||
    //                 $row->menu_sort < $menus[$row->menu_id]['menu_sort'])) {
    //                 $menus[$row->menu_id]['menu_sort'] = $row->menu_sort;
    //             }

    //             if (!empty($row->sub_menu_id)) {
    //                 $menus[$row->menu_id]['submenus'][] = [
    //                     'sub_menu_id'     => $row->sub_menu_id,
    //                     'sub_menu_name'   => $row->sub_menu_name,
    //                     'sub_menu_routes' => $subMenuRoutes[$row->sub_menu_id] ?? null,
    //                     'sub_menu_sort'   => $row->sub_menu_sort,
    //                 ];
    //             }
    //         }

    //         // Final, explicit ordering in PHP (does not depend on row order above).
    //         $menus = array_values($menus);

    //         usort($menus, fn ($a, $b) =>
    //             [$a['menu_sort'] ?? PHP_INT_MAX, $a['menu_name'], $a['menu_id']]
    //             <=>
    //             [$b['menu_sort'] ?? PHP_INT_MAX, $b['menu_name'], $b['menu_id']]
    //         );

    //         foreach ($menus as &$menu) {
    //             usort($menu['submenus'], fn ($a, $b) =>
    //                 [$a['sub_menu_sort'] ?? PHP_INT_MAX, $a['sub_menu_name'], $a['sub_menu_id']]
    //                 <=>
    //                 [$b['sub_menu_sort'] ?? PHP_INT_MAX, $b['sub_menu_name'], $b['sub_menu_id']]
    //             );
    //         }
    //         unset($menu); // break the reference

    //         return $this->getResponse($menus, $message);
    //     } catch (Throwable $e) {
    //         return $this->sendServerError($e, 'An error occurred while retrieving menu access.');
    //     }
    // }


}