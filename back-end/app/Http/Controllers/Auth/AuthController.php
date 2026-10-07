<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Registration\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Get whether public registration is currently open.
     */
    public function registrationStatus(RegistrationStatus $registrationStatus): JsonResponse
    {
        return response()
            ->json([
                'enabled' => $registrationStatus->isEnabled(),
                'show_notice' => $registrationStatus->shouldShowDisabledNotice(),
            ])
            ->header('Cache-Control', 'no-store, private');
    }

    /**
     * Register a new user.
     */
    public function register(Request $request, RegistrationStatus $registrationStatus): JsonResponse
    {
        $registrationStatus->ensureEnabled();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->json([
            'message' => __('messages.registration_successful'),
            'user' => $user,
        ], 201);
    }

    /**
     * Login the user.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::validate($credentials)) {
            return response()->json([
                'message' => __('messages.invalid_credentials'),
            ], 401);
        }

        /** @var User|null $user */
        $user = Auth::getProvider()->retrieveByCredentials($credentials);

        if ($user && in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))
            && method_exists($user, 'hasEnabledTwoFactorAuthentication')
            && $user->hasEnabledTwoFactorAuthentication()) {
            if ($request->hasSession()) {
                $request->session()->put([
                    'login.id' => $user->getKey(),
                    'login.remember' => $request->boolean('remember'),
                ]);
            }

            return response()->json([
                'two_factor' => true,
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json([
            'message' => __('messages.login_successful'),
            'user' => Auth::user(),
        ]);
    }

    /**
     * Logout the user.
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        Auth::guard('web')->logout();

        Auth::forgetGuards();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => __('messages.logout_successful'),
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,'.$request->user()->id],
            'avatar' => ['sometimes', 'nullable', 'string', 'max:2048'],
        ]);

        /** @var User $user */
        $user = $request->user();

        if (array_key_exists('avatar', $validated) && $validated['avatar'] !== $user->avatar) {
            $this->deleteStoredAvatar($user->avatar);
        }

        $user->update($validated);

        return response()->json([
            'message' => __('messages.profile_updated'),
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Upload and update user avatar image.
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $this->deleteStoredAvatar($user->avatar);

        $path = $validated['avatar']->store('avatars', 'public');
        $url = Storage::disk('public')->url($path);

        $user->update(['avatar' => $url]);

        return response()->json([
            'message' => __('messages.avatar_updated'),
            'avatar' => $url,
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Remove user avatar.
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->deleteStoredAvatar($user->avatar);

        $user->update(['avatar' => null]);

        return response()->json([
            'message' => __('messages.avatar_deleted'),
            'avatar' => null,
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Clean up a previously uploaded local avatar from public storage.
     */
    private function deleteStoredAvatar(?string $avatarUrl): void
    {
        if (! $avatarUrl) {
            return;
        }

        $parsed = parse_url($avatarUrl, PHP_URL_PATH);
        if ($parsed && str_contains($parsed, '/storage/avatars/')) {
            $relativePath = 'avatars/'.basename($parsed);
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => __('messages.password_updated'),
        ]);
    }
}
