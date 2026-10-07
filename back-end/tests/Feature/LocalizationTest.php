<?php

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('API localization via Accept-Language', function () {
    test('validation errors return in English by default', function () {
        $this->postJson('/api/register', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password'])
            ->assertJsonPath('errors.name.0', 'The name field is required.');
    });

    test('validation errors return in French when requested', function () {
        $this->withHeader('Accept-Language', 'fr')
            ->postJson('/api/register', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password'])
            ->assertJsonPath('errors.name.0', 'Le champ nom est obligatoire.');
    });

    test('validation errors return in Arabic when requested', function () {
        $this->withHeader('Accept-Language', 'ar')
            ->postJson('/api/register', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password'])
            ->assertJsonPath('errors.name.0', 'الحقل الاسم مطلوب.');
    });

    test('invalid login credentials error returns in French', function () {
        $this->withHeader('Accept-Language', 'fr')
            ->postJson('/api/login', [
                'email' => 'nobody@example.com',
                'password' => 'WrongPassword123!',
            ])
            ->assertStatus(401)
            ->assertJson(['message' => 'Identifiants invalides']);
    });

    test('invalid login credentials error returns in Arabic', function () {
        $this->withHeader('Accept-Language', 'ar')
            ->postJson('/api/login', [
                'email' => 'nobody@example.com',
                'password' => 'WrongPassword123!',
            ])
            ->assertStatus(401)
            ->assertJson(['message' => 'بيانات الاعتماد غير صحيحة']);
    });

    test('registration disabled error returns in French', function () {
        Setting::factory()->registrationDisabled()->create();

        $this->withHeader('Accept-Language', 'fr')
            ->postJson('/api/register', [
                'name' => 'Jean Dupont',
                'email' => 'jean@example.com',
                'password' => 'ValidPass123!#',
                'password_confirmation' => 'ValidPass123!#',
            ])
            ->assertStatus(403)
            ->assertJson(['message' => "L'inscription est actuellement désactivée."]);
    });

    test('registration disabled error returns in Arabic', function () {
        Setting::factory()->registrationDisabled()->create();

        $this->withHeader('Accept-Language', 'ar')
            ->postJson('/api/register', [
                'name' => 'أحمد',
                'email' => 'ahmed@example.com',
                'password' => 'ValidPass123!#',
                'password_confirmation' => 'ValidPass123!#',
            ])
            ->assertStatus(403)
            ->assertJson(['message' => 'التسجيل معطّل حاليًا.']);
    });

    test('successful register message returns in French', function () {
        $this->withHeader('Accept-Language', 'fr')
            ->postJson('/api/register', [
                'name' => 'Jean Dupont',
                'email' => 'jean@example.com',
                'password' => 'ValidPass123!#',
                'password_confirmation' => 'ValidPass123!#',
            ])
            ->assertStatus(201)
            ->assertJson(['message' => 'Inscription réussie']);
    });

    test('successful register message returns in Arabic', function () {
        $this->withHeader('Accept-Language', 'ar')
            ->postJson('/api/register', [
                'name' => 'أحمد',
                'email' => 'ahmed@example.com',
                'password' => 'ValidPass123!#',
                'password_confirmation' => 'ValidPass123!#',
            ])
            ->assertStatus(201)
            ->assertJson(['message' => 'تم التسجيل بنجاح']);
    });

    test('unsupported locale falls back safely to default English without error', function () {
        $this->withHeader('Accept-Language', 'ja,zh;q=0.9')
            ->postJson('/api/register', [])
            ->assertStatus(422)
            ->assertJsonPath('errors.name.0', 'The name field is required.');
    });

    test('response includes Vary Accept-Language header', function () {
        $response = $this->withHeader('Accept-Language', 'fr')
            ->getJson('/api/registration-status');

        expect((string) $response->headers->get('Vary'))->toContain('Accept-Language');
    });
});
