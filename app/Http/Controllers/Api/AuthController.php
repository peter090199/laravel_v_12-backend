<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\AccessRight;

class AuthController extends BaseController
{
    // SIGN UP
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) { return response()->json(['message' => $validator->errors()->first(),], 422);}
         // ONLY get role-user
        $accessRight = AccessRight::where('access_right_name','role-user')->first();
        if (!$accessRight) {
             return $this->sendServerError($accessRight,'role-user access right not found');
        }
        $user = User::create([
            'user_code'         => $this->generateNextUserCode(),
            'username'          => $request->username,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            // DEFAULT ROLE
            'access_right_id'   => $accessRight->id,
            'access_right_name' => $accessRight->access_right_name,
        ]);
        return $this->sendResponse( $user,'Account register successfully', 201);
    }

    // SIGN IN
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid login credentials'
            ], 401);
        }

        // Revoke all existing tokens before issuing a new one
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse(
            null,
            'Logged out successfully',
            200
        );
    }
    // GET LOGGED-IN USER
    public function profile(Request $request)
    {
        $user = $request->user()->only([
            'id', 'user_code', 'username', 'email', 'contact', 'address', 'avatar',
            'email_verified_at', 'created_at', 'updated_at'
        ]);

        return response()->json([
            'user' => $user,
            'avatar_url' => isset($user['avatar']) && $user['avatar']
                ? asset('storage/' . $user['avatar'])
                : null,
        ], 200);
    }

    /**
     * Generates the next sequential user code: 101, 102, 103, ...
     * Locks the table while reading the current max so two simultaneous
     * registrations can't both grab the same next number.
     */
    private function generateNextUserCode(): string
    {
        return DB::transaction(function () {
            $lastCode = User::lockForUpdate()
                ->max(DB::raw('CAST(user_code AS UNSIGNED)'));
            $next = $lastCode ? $lastCode + 1 : 101;
            return (string) $next;
        });
    }
}