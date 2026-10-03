<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends BaseController
{
    /**
     * Get the authenticated user's profile.
     */
    public function profile()
    {
        return $this->profileResponse(
            Auth::user(),
            'Profile retrieved successfully.'
        );
    }

    /**
     * Update text fields (no file upload here).
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'username' => [
                'required', 'string', 'min:3', 'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'contact' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
        ]);

        $user->fill($validated);

        // A changed email must be verified again
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $this->profileResponse(
            $user->fresh(),
            'Profile updated successfully.'
        );
    }

    /**
     * Upload a new avatar.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        $user = Auth::user();
        $oldPath = $user->avatar;

        $file = $request->file('avatar');

        // Unique name so browsers never serve a cached old avatar
        $filename = Str::uuid() . '.' . $file->extension();

        $path = $file->storeAs("users/{$user->id}/avatar", $filename, 'public');

        $user->avatar = $path;
        $user->save();

        // Delete the old file only after the new one is saved
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return $this->profileResponse(
            $user->fresh(),
            'Avatar updated successfully.'
        );
    }

    /**
     * Same response shape for every endpoint.
     */
    private function profileResponse(User $user, string $message)
    {
        return $this->getResponse(
            [
                'user'       => $user,
                'avatar_url' => $user->avatar
                    ? Storage::disk('public')->url($user->avatar)
                    : null,
            ],
            $message,
            200
        );
    }
}