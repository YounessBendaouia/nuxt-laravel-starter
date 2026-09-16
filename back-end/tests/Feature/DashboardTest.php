<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can retrieve dashboard statistics', function () {
    User::factory()->count(3)->create();
    $user = User::first();

    $response = $this->actingAs($user)->getJson('/api/dashboard/stats');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'total_users',
            'recent_users' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                ],
            ],
            'monthly_signups',
        ])
        ->assertJson([
            'total_users' => 3,
        ]);
});

test('unauthenticated user cannot retrieve dashboard statistics', function () {
    $response = $this->getJson('/api/dashboard/stats');

    $response->assertStatus(401);
});
