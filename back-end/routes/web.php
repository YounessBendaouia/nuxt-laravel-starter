<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Named fallback route for login (redirects unauthenticated web requests to Nuxt frontend)
Route::get('/login', function () {
    $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
    return redirect($frontendUrl . '/auth/login');
})->name('login');

// Named fallback route for password.reset (redirects to Nuxt frontend reset-password page)
Route::get('/reset-password/{token}', function (Request $request, string $token) {
    $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
    $email = $request->query('email', '');
    return redirect($frontendUrl . '/auth/reset-password?token=' . $token . '&email=' . urlencode($email));
})->name('password.reset');

// Secure Email Verification route (Security-first: cryptographically signed URL + timing-safe hash check + rate limiting)
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));

    // 1. Verify valid cryptographic HMAC signature and expiration
    if (! $request->hasValidSignature()) {
        return redirect($frontendUrl . '/auth/login?verified=invalid');
    }

    // 2. Locate user safely
    $user = User::find($id);
    if (! $user) {
        return redirect($frontendUrl . '/auth/login?verified=invalid');
    }

    // 3. Timing-safe verification of email verification hash
    if (! hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
        return redirect($frontendUrl . '/auth/login?verified=invalid');
    }

    // 4. Mark email as verified if not yet verified
    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    return redirect($frontendUrl . '/dashboard?verified=1');
})->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
