<?php

namespace App\Http\Controllers\Api\SharedRoutine;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class BaseController extends Controller
{
    public function sendMessage(string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], $code);
    }
    public function sendResponse($result, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], $code);
    }
    public function  getResponse($result, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $result,
        ], $code);
    }

    /**
     * Success response for paginated results.
     */
    public function sendPaginated(LengthAwarePaginator $paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $paginator->items(),
            // 'meta'    => [
            //     'current_page' => $paginator->currentPage(),
            //     'per_page'     => $paginator->perPage(),
            //     'total'        => $paginator->total(),
            //     'last_page'    => $paginator->lastPage(),
            //     'from'         => $paginator->firstItem(),
            //     'to'           => $paginator->lastItem(),
            // ],
        ], 200);
    }



    /**
     * Validation error response.
     */
    public function sendValidationError($errors, string $message = 'Validation Error'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], 422);
    }

    /**
     * 404 Not Found response.
     */
    public function sendNotFound(string $message = 'Resource not found'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 404);
    }

    /**
     * Generic error response.
     */
    public function sendError(string $message = 'Error', $errors = [], int $code = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * 500 Server error response — logs the exception too.
     */
    public function sendServerError(Throwable $e, string $message = 'Server Error'): JsonResponse
    {
        report($e);

        return response()->json([
            'success' => false,
            'message' => $message,
            'error'   => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }


    private function rules(bool $sometimes = false, ?string $ignoreId = null): array
    {
        $rule = $sometimes ? 'sometimes' : 'required';
        $uniqueRule = Rule::unique('menus', 'menu_name');
        if ($ignoreId) {
            $uniqueRule = $uniqueRule->ignore($ignoreId);
        }
        return [
            'menu_name'          => [$rule, 'string', 'max:300', 'min:1', $uniqueRule],
            'record_status'      => ['nullable', 'string', 'max:15'],
            'enterprise'         => ['nullable', 'string', 'max:15'],
            'standard'           => ['nullable', 'string', 'max:15'],
            'express'            => ['nullable', 'string', 'max:15'],
            'project_enterprise' => ['nullable', 'string', 'max:50'],
            'project_standard'   => ['nullable', 'string', 'max:50'],
            'project_express'    => ['nullable', 'string', 'max:50'],
        ];
    }
}
