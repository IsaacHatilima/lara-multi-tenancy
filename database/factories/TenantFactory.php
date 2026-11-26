<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
        ];
    }

    /**
     * Fill in the company's contact, registration and address details.
     */
    public function withCompanyDetails(): static
    {
        return $this->state(fn (array $attributes): array => [
            'legal_name' => $attributes['name'],
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'website' => fake()->url(),
            'registration_number' => fake()->bothify('REG-########'),
            'tax_number' => fake()->bothify('TAX-#########'),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->optional()->numerify('Suite ###'),
            'city' => fake()->city(),
            'state' => ucfirst(fake()->word()),
            'postal_code' => fake()->postcode(),
            'country_code' => fake()->countryCode(),
            'timezone' => fake()->timezone(),
            'locale' => 'en',
        ]);
    }
}
