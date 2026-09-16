<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

uses(RefreshDatabase::class);

test('authenticated user can enable two factor authentication', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->postJson('/user/two-factor-authentication');

    $response->assertStatus(200);
    expect($user->fresh()->two_factor_secret)->not->toBeNull();
});

test('authenticated user can view qr code and recovery codes after enabling 2fa', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->postJson('/user/two-factor-authentication');

    $qrResponse = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->getJson('/user/two-factor-qr-code');

    $qrResponse->assertStatus(200)
        ->assertJsonStructure(['svg']);

    $codesResponse = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->getJson('/user/two-factor-recovery-codes');

    $codesResponse->assertStatus(200);
    expect($codesResponse->json())->toBeArray()->not->toBeEmpty();

    $regenResponse = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->postJson('/user/two-factor-recovery-codes');

    $regenResponse->assertStatus(200);
});

test('authenticated user can confirm two factor authentication with valid code', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->postJson('/user/two-factor-authentication');

    $this->mock(TwoFactorAuthenticationProvider::class, function ($mock) {
        $mock->shouldReceive('verify')->andReturn(true);
    });

    $response = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->postJson('/user/confirmed-two-factor-authentication', [
            'code' => '123456',
        ]);

    $response->assertStatus(200);
    expect($user->fresh()->two_factor_confirmed_at)->not->toBeNull();
    expect($user->fresh()->hasEnabledTwoFactorAuthentication())->toBeTrue();
});

test('login requires two factor challenge when 2fa is confirmed', function () {
    $user = User::factory()->withTwoFactor()->create([
        'email' => 'twofactor@example.com',
        'password' => Hash::make('K9#vX!82mZ$qL9*wP'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'twofactor@example.com',
        'password' => 'K9#vX!82mZ$qL9*wP',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'two_factor' => true,
        ]);

    $this->assertGuest();
});

test('user can complete login using two factor recovery code', function () {
    $user = User::factory()->withTwoFactor()->create([
        'email' => 'twofactor@example.com',
        'password' => Hash::make('K9#vX!82mZ$qL9*wP'),
    ]);

    // Initial login attempts returns two_factor: true and stages login in session
    $this->postJson('/api/login', [
        'email' => 'twofactor@example.com',
        'password' => 'K9#vX!82mZ$qL9*wP',
    ]);

    // Challenge with recovery code
    $response = $this->withSession(['login.id' => $user->id])
        ->postJson('/two-factor-challenge', [
            'recovery_code' => 'code-1',
        ]);

    $response->assertStatus(204);
    $this->assertAuthenticatedAs($user);
});

test('authenticated user can disable two factor authentication', function () {
    $user = User::factory()->withTwoFactor()->create();

    $response = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->deleteJson('/user/two-factor-authentication');

    $response->assertStatus(200);
    expect($user->fresh()->two_factor_secret)->toBeNull();
    expect($user->fresh()->two_factor_recovery_codes)->toBeNull();
});

test('user profile endpoint exposes two_factor_confirmed_at and two_factor_enabled accurately', function () {
    $plainUser = User::factory()->create();

    $res1 = $this->actingAs($plainUser)->getJson('/api/user');
    $res1->assertStatus(200)
        ->assertJson([
            'id' => $plainUser->id,
            'two_factor_confirmed_at' => null,
            'two_factor_enabled' => false,
        ]);
    expect($res1->json())->not->toHaveKey('two_factor_secret')
        ->and($res1->json())->not->toHaveKey('two_factor_recovery_codes');

    $twoFactorUser = User::factory()->withTwoFactor()->create();

    $res2 = $this->actingAs($twoFactorUser)->getJson('/api/user');
    $res2->assertStatus(200)
        ->assertJson([
            'id' => $twoFactorUser->id,
            'two_factor_enabled' => true,
        ]);
    expect($res2->json('two_factor_confirmed_at'))->not->toBeNull();
    expect($res2->json())->not->toHaveKey('two_factor_secret')
        ->and($res2->json())->not->toHaveKey('two_factor_recovery_codes');
});
