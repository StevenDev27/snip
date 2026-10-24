<?php

namespace Database\Factories;

use App\Models\Link;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Link>
 */
class LinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Link::generateCode(),
            'original_url' => fake()->url(),
            'expires_at' => null,
            'owner_token' => null,
        ];
    }

    /**
     * Indicate the visitor that created the link.
     */
    public function ownedBy(string $ownerToken): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_token' => $ownerToken,
        ]);
    }

    /**
     * Indicate that the link has already expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
