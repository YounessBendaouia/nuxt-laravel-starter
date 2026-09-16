<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated user can update profile information', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    $response = $this->actingAs($user)->putJson('/api/user/profile', [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'avatar' => 'https://example.com/avatar.png',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Profile updated',
            'user' => [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'avatar' => 'https://example.com/avatar.png',
            ],
        ]);

    expect($user->fresh()->name)->toBe('Updated Name')
        ->and($user->fresh()->email)->toBe('updated@example.com')
        ->and($user->fresh()->avatar)->toBe('https://example.com/avatar.png');
});

test('profile update fails if email is already taken by another user', function () {
    User::factory()->create(['email' => 'other@example.com']);
    $user = User::factory()->create(['email' => 'me@example.com']);

    $response = $this->actingAs($user)->putJson('/api/user/profile', [
        'name' => 'Me',
        'email' => 'other@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('authenticated user can update password with valid current password and new strong password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('CurrentP@ssw0rd!123'),
    ]);

    $response = $this->actingAs($user)->putJson('/api/user/password', [
        'current_password' => 'CurrentP@ssw0rd!123',
        'password' => 'K9#vX!82mZ$qL9*wP',
        'password_confirmation' => 'K9#vX!82mZ$qL9*wP',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Password updated successfully',
        ]);

    expect(Hash::check('K9#vX!82mZ$qL9*wP', $user->fresh()->password))->toBeTrue();
});

test('password update fails when current password is wrong', function () {
    $user = User::factory()->create([
        'password' => Hash::make('CurrentP@ssw0rd!123'),
    ]);

    $response = $this->actingAs($user)->putJson('/api/user/password', [
        'current_password' => 'WrongCurrentPassword',
        'password' => 'K9#vX!82mZ$qL9*wP',
        'password_confirmation' => 'K9#vX!82mZ$qL9*wP',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['current_password']);
});

test('password update fails when new password is weak', function () {
    $user = User::factory()->create([
        'password' => Hash::make('CurrentP@ssw0rd!123'),
    ]);

    $response = $this->actingAs($user)->putJson('/api/user/password', [
        'current_password' => 'CurrentP@ssw0rd!123',
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});
