<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('users can authenticate using login endpoint', function () {
    $user = User::factory()->create([
        'email' => 'alex@example.com',
        'password' => Hash::make('K9#vX!82mZ$qL9*wP'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'alex@example.com',
        'password' => 'K9#vX!82mZ$qL9*wP',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
            ],
        ])
        ->assertJson([
            'message' => 'Login successful',
            'user' => [
                'email' => 'alex@example.com',
            ],
        ]);

    $this->assertAuthenticatedAs($user);
});

test('users cannot authenticate with invalid password', function () {
    User::factory()->create([
        'email' => 'alex@example.com',
        'password' => Hash::make('K9#vX!82mZ$qL9*wP'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'alex@example.com',
        'password' => 'WrongPassword123!',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Invalid credentials',
        ]);

    $this->assertGuest();
});

test('users can logout and session is invalidated', function () {
    $user = User::factory()->create([
        'email' => 'alex@example.com',
        'password' => Hash::make('K9#vX!82mZ$qL9*wP'),
    ]);

    $this->postJson('/api/login', [
        'email' => 'alex@example.com',
        'password' => 'K9#vX!82mZ$qL9*wP',
    ]);

    $this->assertAuthenticatedAs($user);

    $response = $this->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Logged out successfully',
        ]);

    $this->assertGuest('web');
});

test('authenticated user can retrieve their profile via /api/user', function () {
    $user = User::factory()->create([
        'name' => 'Alex Morgan',
        'email' => 'alex@example.com',
    ]);

    $response = $this->actingAs($user)->getJson('/api/user');

    $response->assertStatus(200)
        ->assertJson([
            'id' => $user->id,
            'name' => 'Alex Morgan',
            'email' => 'alex@example.com',
        ]);
});

test('unauthenticated user cannot retrieve /api/user', function () {
    $response = $this->getJson('/api/user');

    $response->assertStatus(401);
});
