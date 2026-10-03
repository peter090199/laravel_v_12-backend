<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AccountUserController extends BaseController
{
    // GET /api/account-users
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::query();

            if ($request->filled('search')) {
                $s = '%' . $request->search . '%';

                $query->where(fn ($q) => $q
                    ->where('username', 'like', $s)
                    ->orWhere('email', 'like', $s)
                    ->orWhere('user_code', 'like', $s)
                    ->orWhere('contact', 'like', $s));
            }

            if ($request->filled('access_right_id')) {
                $query->where('access_right_id', $request->access_right_id);
            }

            $users = $query
                ->orderBy('username')
                ->get([
                    'id', 'user_code', 'access_right_id', 'access_right_name',
                    'username', 'email', 'contact', 'address', 'avatar',
                    'email_verified_at', 'created_at', 'updated_at',
                ])
                ->map(function (User $user) {
                    $data = $user->toArray();
                    $data['avatar_url'] = $user->avatar
                        ? Storage::disk('public')->url($user->avatar)
                        : null;

                    return $data;
                })
                ->values();

            // Returns a plain array in "data"
            return $this->getResponse($users, 'Users retrieved successfully');
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    // DELETE /api/account-users/{id}
    public function destroy(string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return $this->sendNotFound('User not found');
        }

        if ($user->id === Auth::id()) {
            return $this->sendMessage('You cannot delete your own account.', 422);
        }

        try {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->tokens()->delete(); // revoke Sanctum tokens
            $user->delete();

            return $this->sendMessage('User deleted successfully');
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }
}