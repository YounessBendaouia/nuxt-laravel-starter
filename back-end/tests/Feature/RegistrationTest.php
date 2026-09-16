<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('users can register with valid credentials and strong password', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'K9#vX!82mZ$qL9*wP',
        'password_confirmation' => 'K9#vX!82mZ$qL9*wP',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'message' => 'Registration successful',
            'user' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
        'name' => 'Jane Doe',
    ]);

    $this->assertAuthenticated();
});

test('registration fails if password is too simple or weak', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'simple',
        'password_confirmation' => 'simple',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);

    $this->assertGuest();
});

test('registration fails if passwords do not match', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'K9#vX!82mZ$qL9*wP',
        'password_confirmation' => 'Mismatch123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);

    $this->assertGuest();
});

test('registration fails if email is already taken', function () {
    User::factory()->create([
        'email' => 'taken@example.com',
    ]);

    $response = $this->postJson('/api/register', [
        'name' => 'Another User',
        'email' => 'taken@example.com',
        'password' => 'K9#vX!82mZ$qL9*wP',
        'password_confirmation' => 'K9#vX!82mZ$qL9*wP',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

    $this->assertGuest();
});
