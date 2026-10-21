<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\SubMenuController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\TodoController;
use App\Http\Controllers\Api\WebsiteChatController;
use App\Http\Controllers\Api\AccessRightController;
use App\Http\Controllers\Api\MenuAccessController;
use App\Http\Controllers\Api\AccountUserController;
use App\Http\Controllers\Api\DatabaseBackupController;
use App\Http\Controllers\Api\Files\DepartmentController;
use App\Http\Controllers\Api\Files\ShiftController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\Files\DesignationController;
use App\Http\Controllers\Api\Files\EmployeeController;
use App\Http\Controllers\Api\Files\SssTableController;
use App\Http\Controllers\Api\Files\PhilhealthTableController;
use App\Http\Controllers\Api\Files\PagibigTableController;
use App\Http\Controllers\Api\Files\TaxTableController;
use App\Http\Controllers\Api\Files\ConfigFileController;

Route::post('/website-chat', [WebsiteChatController::class, 'chat']);
Route::prefix('auth')->group(function () {
    // ---------- Public routes (no auth required) ----------
    Route::post('/register', [AuthController::class, 'register'])->name('api.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.login');
    // ---------- Protected routes (require valid Sanctum token) ----------
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
        Route::get('/profile', [AuthController::class, 'profile'])->name('api.profile');
        Route::get('/user', fn(Request $request) => $request->user())->name('api.user');
        Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
        // Menu/submenu tree for CURRENTLY LOGGED-IN USER
        Route::get('/my-menus', [MenuAccessController::class, 'index'])->name('api.my-menus');
        // Menu/submenu tree for ANY access right ID
        Route::get('/access-rights/menus/{accessRightId}', [MenuAccessController::class, 'byAccessRight'])->name('api.access-right-menus');
    });
});
// Public: license check + activation
Route::prefix('license')->group(function () {
    Route::get('status', [LicenseController::class, 'status']);
    Route::post('activate', [LicenseController::class, 'activate'])->middleware('throttle:5,1');
});
// Everything below requires an active license
Route::middleware('licensed')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);

        // ...your protected routes here
    });
});
Route::middleware('auth:sanctum')->prefix('todos')->group(function () {
    Route::post('/createTodo', [TodoController::class, 'createTodo']);
    Route::post('/updateTodo/{id}', [TodoController::class, 'updateTodo']);
    Route::get('/getTodo', [TodoController::class, 'index']);
    Route::get('/getTodoById/{id}', [TodoController::class, 'show']);
    Route::delete('/delete/{id}', [TodoController::class, 'deleteTodo']);
});
Route::middleware('auth:sanctum')->prefix('menu')->group(function () {
    Route::get('/',              [MenuController::class, 'index']);
    Route::post('/createmenu',   [MenuController::class, 'store']);
    Route::get('/{id}',          [MenuController::class, 'show']);
    Route::put('/{id}',          [MenuController::class, 'update']);
    Route::delete('/{id}',       [MenuController::class, 'destroy']);
});
Route::middleware('auth:sanctum')->prefix('submenu')->group(function () {
    Route::get('/',              [SubMenuController::class, 'index']);
    Route::post('/createsubmenu',   [SubMenuController::class, 'store']);
    Route::get('/{id}',          [SubMenuController::class, 'show']);
    Route::put('updateBySubmenuId/{id}', [SubMenuController::class, 'updateBySubmenuId']);
    Route::delete('/{id}', [SubMenuController::class, 'destroy']);
});
Route::middleware('auth:sanctum')->prefix('access-rights')->group(function () {
    Route::get('getAllAccessRight', [AccessRightController::class, 'index']);
    Route::get('getAccessRightBy/{id}', [AccessRightController::class, 'show']);
    Route::post('saveAccessRight', [AccessRightController::class, 'store']);
    Route::put('updateAccessRight/{id}', [AccessRightController::class, 'update']);
    // Route::post('save', [AccessRightController::class, 'save']);
    Route::delete('delete/{id}', [AccessRightController::class, 'destroy']);
});
Route::middleware('auth:sanctum')->prefix('role-permissions')->group(function () {
    Route::get('getAllRolePermissions', [RolePermissionController::class, 'index']);
    Route::get('getAllRolePermissionsById/{id}', [RolePermissionController::class, 'show']);
    Route::post('saveRolePermissions', [RolePermissionController::class, 'store']);
    Route::put('updateRolePermissionsById/{id}', [RolePermissionController::class, 'update']);
    // Route::post('save', [RolePermissionController::class, 'save']);
    Route::delete('deleteRolePermissionsById/{id}', [RolePermissionController::class, 'destroy']);
    Route::post('saveRolePermissionsSync', [RolePermissionController::class, 'sync']);
});
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::get('account-users', [AccountUserController::class, 'index']);
    Route::delete('account-users/{id}', [AccountUserController::class, 'destroy']);
    Route::post('reset-password', [AccountUserController::class, 'resetPassword']);
});
Route::prefix('auth/database')->middleware('auth:sanctum')->group(function () {
    Route::get('backups', [DatabaseBackupController::class, 'index']);
    Route::post('backup', [DatabaseBackupController::class, 'backup']);
    Route::post('import', [DatabaseBackupController::class, 'import']);
    Route::post('backups/{id}/restore', [DatabaseBackupController::class, 'restore']);
    Route::get('backups/{id}/download', [DatabaseBackupController::class, 'download']);
    Route::delete('backups/{id}', [DatabaseBackupController::class, 'destroy']);
});
Route::prefix('shifts')->middleware('auth:sanctum')->group(function () {
    Route::get('getShifts', [ShiftController::class, 'index']);
    Route::post('saveShifts', [ShiftController::class, 'store']);
    Route::put('updateShifts/{id}', [ShiftController::class, 'update']);
    Route::delete('deleteShifts/{id}', [ShiftController::class, 'destroy']);
});
Route::prefix('departments')->middleware('auth:sanctum')->group(function () {
    Route::get('getDepartments', [DepartmentController::class, 'index']);
    Route::get('getDepartment/{id}', [DepartmentController::class, 'show']);
    Route::post('saveDepartment', [DepartmentController::class, 'store']);
    Route::put('updateDepartment/{id}', [DepartmentController::class, 'update']);
    Route::delete('deleteDepartment/{id}', [DepartmentController::class, 'destroy']);
});
Route::prefix('designations')->middleware('auth:sanctum')->group(function () {
    Route::get('getDesignations', [DesignationController::class, 'index']);
    Route::get('getDesignation/{id}', [DesignationController::class, 'show']);
    Route::post('saveDesignation', [DesignationController::class, 'store']);
    Route::put('updateDesignation/{id}', [DesignationController::class, 'update']);
    Route::delete('deleteDesignation/{id}', [DesignationController::class, 'destroy']);
});
Route::prefix('employees')->middleware('auth:sanctum')->group(function () {
    Route::get('getEmployees', [EmployeeController::class, 'index']);
    Route::get('getEmployee/{id}', [EmployeeController::class, 'show']);
    Route::get('getByEmpID/{empID}', [EmployeeController::class, 'getByEmpID']);
    Route::put('updateEmployeeByEmpID/{empID}', [EmployeeController::class, 'updateEmployeeByEmpID']);
    Route::post('saveEmployee', [EmployeeController::class, 'store']);
    Route::match(['put', 'post'], 'updateEmployee/{id}', [EmployeeController::class, 'update']);
    Route::delete('deleteEmployee/{id}', [EmployeeController::class, 'destroy']);
});
Route::prefix('sss')->middleware('auth:sanctum')->group(function () {
    Route::get('getSssTable', [SssTableController::class, 'index']); // Get all SSS contribution brackets
    Route::get('getSssTable/{id}', [SssTableController::class, 'show']); // Get one SSS bracket by ID
    Route::get('getSssBySalary/{salary}', [SssTableController::class, 'lookup']); // Get SSS contribution bracket by salary
    Route::post('saveSssTable', [SssTableController::class, 'store']); // Create SSS bracket
    Route::put('updateSssTable/{id}', [SssTableController::class, 'update']); // Update SSS bracket by ID
    Route::delete('deleteSssTable/{id}', [SssTableController::class, 'destroy']); // Delete SSS bracket by ID
});
Route::prefix('philhealth')->middleware('auth:sanctum')->group(function () {
    Route::get('getPhilhealthTable', [PhilhealthTableController::class, 'index']); // Get all PhilHealth contribution brackets
    Route::get('getPhilhealthTable/{id}', [PhilhealthTableController::class, 'show']); // Get one PhilHealth bracket by ID
    Route::get('getPhilhealthBySalary/{salary}', [PhilhealthTableController::class, 'lookup']); // Get PhilHealth bracket by salary
    Route::post('savePhilhealthTable', [PhilhealthTableController::class, 'store']); // Create PhilHealth bracket
    Route::put('updatePhilhealthTable/{id}', [PhilhealthTableController::class, 'update']); // Update PhilHealth bracket by ID
    Route::delete('deletePhilhealthTable/{id}', [PhilhealthTableController::class, 'destroy']); // Delete PhilHealth bracket by ID
});
Route::prefix('pagibig')->middleware('auth:sanctum')->group(function () {
    Route::get('getPagibigTable', [PagibigTableController::class, 'index']); // Get all Pag-IBIG contribution brackets
    Route::get('getPagibigTable/{id}', [PagibigTableController::class, 'show']); // Get one Pag-IBIG bracket by ID
    Route::get('getPagibigBySalary/{salary}', [PagibigTableController::class, 'lookup']); // Get Pag-IBIG bracket by salary
    Route::post('savePagibigTable', [PagibigTableController::class, 'store']); // Create Pag-IBIG bracket
    Route::put('updatePagibigTable/{id}', [PagibigTableController::class, 'update']); // Update Pag-IBIG bracket by ID
    Route::delete('deletePagibigTable/{id}', [PagibigTableController::class, 'destroy']); // Delete Pag-IBIG bracket by ID
});
Route::prefix('tax')->middleware('auth:sanctum')->group(function () {
    Route::get('getTaxTable', [TaxTableController::class, 'index']); // Get all tax brackets
    Route::get('getTaxTable/{id}', [TaxTableController::class, 'show']); // Get one tax bracket by ID
    Route::get('getTaxBySalary/{salary}', [TaxTableController::class, 'lookup']); // Get tax bracket by salary
    Route::post('saveTaxTable', [TaxTableController::class, 'store']); // Create tax bracket
    Route::put('updateTaxTable/{id}', [TaxTableController::class, 'update']); // Update tax bracket by ID
    Route::delete('deleteTaxTable/{id}', [TaxTableController::class, 'destroy']); // Delete tax bracket by ID
});
Route::middleware('auth:sanctum')->prefix('files')->group(function () {
    Route::get('config', [ConfigFileController::class, 'index']);
    Route::get('config/{id}', [ConfigFileController::class, 'show']);
    Route::post('saveconfig', [ConfigFileController::class, 'store']);
    Route::put('updateconfigId/{id}', [ConfigFileController::class, 'update']);
    Route::delete('deleteconfigId/{id}', [ConfigFileController::class, 'destroy']);
});