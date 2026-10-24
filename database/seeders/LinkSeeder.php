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

    /**
     * @var array<int, string>
     */
    private const USER_AGENTS = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Mobile Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64; rv:125.0) Gecko/20100101 Firefox/125.0',
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
            $count = (int) round($popularity * $trend * $weekend * (random_int(60, 140) / 100));
            $lastHour = $daysAgo === 0 ? max(now()->hour, 7) : 23;

            for ($i = 0; $i < $count; $i++) {
                $rows[] = [
                    'link_id' => $link->id,
                    'clicked_at' => $day->copy()->setTime(
                        random_int(7, $lastHour),
                        random_int(0, 59),
                        random_int(0, 59),
                    ),
                    'referer' => self::REFERERS[array_rand(self::REFERERS)],
                    'user_agent' => self::USER_AGENTS[array_rand(self::USER_AGENTS)],
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Click::insert($chunk);
        }
    }
}
