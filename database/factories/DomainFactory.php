<?php

namespace Database\Factories;

use App\Enums\DomainSlot;
use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Domain>
 */
class DomainFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'domain' => fake()->unique()->domainName(),
            'slot' => DomainSlot::Primary,
        ];
    }

    /**
     * Put the domain in the company's secondary slot.
     */
    public function secondary(): static
    {
        return $this->state(['slot' => DomainSlot::Secondary]);
    }

    /**
     * Create one domain in each of the company's two slots.
     */
    public function bothSlots(): static
    {
        return $this->count(2)->sequence(
            ['slot' => DomainSlot::Primary],
            ['slot' => DomainSlot::Secondary],
        );
    }
}
