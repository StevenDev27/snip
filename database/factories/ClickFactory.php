<?php

namespace Database\Factories;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Click>
 */
class ClickFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'link_id' => Link::factory(),
            'clicked_at' => now(),
            'referer' => null,
            'user_agent' => fake()->userAgent(),
        ];
    }

    /**
     * Indicate that the click happened a given number of days ago.
     */
    public function daysAgo(int $days): static
    {
        return $this->state(fn (array $attributes) => [
            'clicked_at' => now()->subDays($days),
        ]);
    }

    /**
     * Indicate the page the visitor came from.
     */
    public function from(string $referer): static
    {
        return $this->state(fn (array $attributes) => [
            'referer' => $referer,
        ]);
    }
}
