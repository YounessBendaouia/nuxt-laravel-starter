<?php

namespace Database\Factories;

use App\Actions\Registration\RegistrationStatus;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'value' => fake()->word(),
        ];
    }

    /**
     * Indicate that public registration is disabled.
     */
    public function registrationDisabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => RegistrationStatus::SETTING_KEY,
            'value' => false,
        ]);
    }

    /**
     * Indicate that public registration is enabled.
     */
    public function registrationEnabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => RegistrationStatus::SETTING_KEY,
            'value' => true,
        ]);
    }
}
