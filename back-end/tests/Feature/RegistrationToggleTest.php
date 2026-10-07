<?php

use App\Actions\Registration\RegistrationStatus;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/**
 * @return array{name: string, email: string, password: string, password_confirmation: string}
 */
function validRegistrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'K9#vX!82mZ$qL9*wP',
        'password_confirmation' => 'K9#vX!82mZ$qL9*wP',
    ], $overrides);
}

describe('registration status endpoint', function () {
    test('reports registration as enabled by default', function () {
        $this->getJson('/api/registration-status')
            ->assertOk()
            ->assertExactJson(['enabled' => true, 'show_notice' => false])
            ->assertHeader('Cache-Control', 'no-store, private');
    });

    test('shows the notice when the env switch is on but the runtime toggle is off', function () {
        Setting::factory()->registrationDisabled()->create();

        $this->getJson('/api/registration-status')
            ->assertOk()
            ->assertExactJson(['enabled' => false, 'show_notice' => true]);
    });

    test('hides the notice when the env kill-switch is off', function () {
        config(['auth.registration.enabled' => false]);

        $this->getJson('/api/registration-status')
            ->assertOk()
            ->assertExactJson(['enabled' => false, 'show_notice' => false]);
    });

    test('hides the notice when the env kill-switch is off even if the runtime toggle is also off', function () {
        config(['auth.registration.enabled' => false]);
        Setting::factory()->registrationDisabled()->create();

        $this->getJson('/api/registration-status')
            ->assertOk()
            ->assertExactJson(['enabled' => false, 'show_notice' => false]);
    });

    test('cannot be used to change the registration status over http', function (string $method) {
        Setting::factory()->registrationDisabled()->create();

        $this->json($method, '/api/registration-status', ['enabled' => true])
            ->assertMethodNotAllowed();

        expect(app(RegistrationStatus::class)->isEnabled())->toBeFalse();
    })->with(['POST', 'PUT', 'PATCH', 'DELETE']);
});

describe('when registration is disabled', function () {
    beforeEach(function () {
        Setting::factory()->registrationDisabled()->create();
    });

    test('the api register endpoint rejects new users', function () {
        $this->postJson('/api/register', validRegistrationPayload())
            ->assertForbidden()
            ->assertJson(['message' => 'Registration is currently disabled.']);

        $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
        $this->assertGuest();
    });

    test('the endpoint does not leak validation errors that could enumerate emails', function () {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/register', validRegistrationPayload(['email' => 'taken@example.com']))
            ->assertForbidden()
            ->assertJsonMissingPath('errors');
    });

    test('the register endpoint is still rate limited', function () {
        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/register', validRegistrationPayload())->assertForbidden();
        }

        $this->postJson('/api/register', validRegistrationPayload())->assertTooManyRequests();
    });
});

describe('fail-closed behaviour', function () {
    test('environment kill-switch overrides an enabled runtime toggle', function () {
        config(['auth.registration.enabled' => false]);
        Setting::factory()->registrationEnabled()->create();

        $this->postJson('/api/register', validRegistrationPayload())->assertForbidden();
        $this->getJson('/api/registration-status')->assertExactJson(['enabled' => false, 'show_notice' => false]);
        $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
    });

    test('a non-boolean kill-switch value is treated as disabled', function () {
        config(['auth.registration.enabled' => 'yes']);

        expect(app(RegistrationStatus::class)->isEnabled())->toBeFalse();
    });

    test('an unexpected stored value is treated as disabled', function (mixed $storedValue) {
        Setting::factory()->create([
            'key' => RegistrationStatus::SETTING_KEY,
            'value' => $storedValue,
        ]);

        expect(app(RegistrationStatus::class)->isEnabled())->toBeFalse();
    })->with([
        'string true' => 'true',
        'integer one' => 1,
        'array' => [[true]],
    ]);

    test('a database failure is treated as disabled', function () {
        Schema::drop('settings');

        $this->postJson('/api/register', validRegistrationPayload())->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
    });
});

describe('when registration is re-enabled', function () {
    test('users can register again', function () {
        Setting::factory()->registrationDisabled()->create();
        app(RegistrationStatus::class)->enable();

        $this->postJson('/api/register', validRegistrationPayload())->assertCreated();

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    });
});

describe('artisan commands', function () {
    test('registration:disable closes registration and writes an audit log entry', function () {
        Log::spy();

        $this->artisan('registration:disable')->assertSuccessful();

        expect(app(RegistrationStatus::class)->isEnabled())->toBeFalse();
        Log::shouldHaveReceived('warning')->once()->withArgs(
            fn (string $message, array $context) => $message === 'Public registration DISABLED via console.'
                && array_key_exists('os_user', $context)
                && array_key_exists('host', $context),
        );
    });

    test('registration:enable opens registration and writes an audit log entry', function () {
        Log::spy();
        Setting::factory()->registrationDisabled()->create();

        $this->artisan('registration:enable')->assertSuccessful();

        expect(app(RegistrationStatus::class)->isEnabled())->toBeTrue();
        Log::shouldHaveReceived('warning')->once()->withArgs(
            fn (string $message) => $message === 'Public registration ENABLED via console.',
        );
    });

    test('registration:enable requires confirmation in production', function () {
        app()->detectEnvironment(fn () => 'production');
        Setting::factory()->registrationDisabled()->create();

        $this->artisan('registration:enable')
            ->expectsConfirmation('You are about to OPEN public registration in production. Continue?', 'no')
            ->assertFailed();

        expect(app(RegistrationStatus::class)->isEnabled())->toBeFalse();
    });

    test('registration:enable can be forced in production', function () {
        app()->detectEnvironment(fn () => 'production');
        Setting::factory()->registrationDisabled()->create();

        $this->artisan('registration:enable', ['--force' => true])->assertSuccessful();

        expect(app(RegistrationStatus::class)->isEnabled())->toBeTrue();
    });

    test('registration:enable warns when the environment kill-switch is off', function () {
        config(['auth.registration.enabled' => false]);

        $this->artisan('registration:enable')
            ->expectsOutputToContain('REGISTRATION_ENABLED=false')
            ->assertSuccessful();

        expect(app(RegistrationStatus::class)->isEnabled())->toBeFalse();
    });

    test('registration:status reports the effective status', function () {
        Setting::factory()->registrationDisabled()->create();

        $this->artisan('registration:status')
            ->expectsOutputToContain('DISABLED')
            ->assertSuccessful();
    });
});
