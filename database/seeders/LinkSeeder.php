<?php

namespace Database\Seeders;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Database\Seeder;

class LinkSeeder extends Seeder
{
    /**
     * Enlaces de ejemplo: visibles para todos los visitantes (sin dueño).
     *
     * @var array<int, array{code: string, url: string, popularity: int}>
     */
    private const DEMOS = [
        ['code' => 'laravl', 'url' => 'https://laravel.com/docs', 'popularity' => 40],
        ['code' => 'pgdocs', 'url' => 'https://www.postgresql.org/docs/', 'popularity' => 25],
        ['code' => 'tailwd', 'url' => 'https://tailwindcss.com/docs', 'popularity' => 15],
        ['code' => 'github', 'url' => 'https://github.com', 'popularity' => 8],
    ];

    /**
     * Los null repetidos simulan visitas directas, que son las más comunes.
     *
     * @var array<int, string|null>
     */
    private const REFERERS = [
        'https://www.google.com/',
        'https://www.google.com/',
        'https://t.co/',
        'https://www.linkedin.com/',
        'https://www.instagram.com/',
        null,
        null,
    ];

    public function run(): void
    {
        foreach (self::DEMOS as $demo) {
            $link = Link::updateOrCreate(
                ['code' => $demo['code']],
                ['original_url' => $demo['url'], 'owner_token' => null, 'expires_at' => null],
            );

            $link->clicks()->delete();

            $this->seedClicks($link, $demo['popularity']);
        }
    }

    private function seedClicks(Link $link, int $popularity): void
    {
        $rows = [];

        for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
            $day = now()->subDays($daysAgo);
            $trend = 0.5 + (29 - $daysAgo) / 29;
            $weekend = $day->isWeekend() ? 0.6 : 1.0;
            $count = (int) round($popularity * $trend * $weekend * fake()->randomFloat(2, 0.6, 1.4));
            $lastHour = $daysAgo === 0 ? max(now()->hour, 7) : 23;

            for ($i = 0; $i < $count; $i++) {
                $rows[] = [
                    'link_id' => $link->id,
                    'clicked_at' => $day->copy()->setTime(
                        fake()->numberBetween(7, $lastHour),
                        fake()->numberBetween(0, 59),
                        fake()->numberBetween(0, 59),
                    ),
                    'referer' => fake()->randomElement(self::REFERERS),
                    'user_agent' => fake()->userAgent(),
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Click::insert($chunk);
        }
    }
}
