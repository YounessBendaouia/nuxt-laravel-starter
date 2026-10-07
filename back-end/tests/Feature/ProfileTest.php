<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

test('authenticated user can upload an avatar image', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

    $response = $this->actingAs($user)->postJson('/api/user/avatar', [
        'avatar' => $file,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['message', 'avatar', 'user']);

    $newAvatar = $user->fresh()->avatar;
    expect($newAvatar)->not->toBeNull();

    $relativePath = 'avatars/'.$file->hashName();
    Storage::disk('public')->assertExists($relativePath);
});

test('avatar upload fails when file is not an image or too large', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $pdfFile = UploadedFile::fake()->create('document.pdf', 100);

    $this->actingAs($user)->postJson('/api/user/avatar', [
        'avatar' => $pdfFile,
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['avatar']);

    $largeFile = UploadedFile::fake()->image('huge.jpg')->size(6000); // 6MB > 5MB limit

    $this->actingAs($user)->postJson('/api/user/avatar', [
        'avatar' => $largeFile,
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['avatar']);
});

test('authenticated user can remove their avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'avatar' => 'https://example.com/avatar.jpg',
    ]);

    $response = $this->actingAs($user)->deleteJson('/api/user/avatar');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Avatar removed successfully',
            'avatar' => null,
        ]);

    expect($user->fresh()->avatar)->toBeNull();
});

test('avatar upload and delete responses respect requested locale', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.png');

    $this->actingAs($user)
        ->withHeader('Accept-Language', 'fr')
        ->postJson('/api/user/avatar', ['avatar' => $file])
        ->assertStatus(200)
        ->assertJson(['message' => 'Avatar mis à jour avec succès']);

    $this->actingAs($user)
        ->withHeader('Accept-Language', 'ar')
        ->deleteJson('/api/user/avatar')
        ->assertStatus(200)
        ->assertJson(['message' => 'تم حذف الصورة الرمزية بنجاح']);
});
