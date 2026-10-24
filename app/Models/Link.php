<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Link extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'original_url', 'expires_at', 'owner_token'];

    protected $casts = ['expires_at' => 'datetime'];

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    /**
     * Enlaces de ejemplo (sin dueño) y los del visitante identificado por el token.
     *
     * @param  Builder<Link>  $query
     */
    public function scopeVisibleTo(Builder $query, ?string $ownerToken): void
    {
        $query->where(function (Builder $query) use ($ownerToken): void {
            $query->whereNull('owner_token');

            if ($ownerToken !== null) {
                $query->orWhere('owner_token', $ownerToken);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public static function generateCode(): string
    {
        do {
            $code = Str::random(6);
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function shortUrl(): string
    {
        return route('links.redirect', $this->code);
    }

    /**
     * Clics por día de los últimos $days días, incluyendo los días sin clics.
     *
     * @return array<string, int> Fecha (Y-m-d) => total de clics
     */
    public function clicksPerDay(int $days = 30): array
    {
        $from = now()->subDays($days - 1)->startOfDay();

        $totals = $this->clicks()
            ->where('clicked_at', '>=', $from)
            ->selectRaw('DATE(clicked_at) as click_date, COUNT(*) as total')
            ->groupBy('click_date')
            ->pluck('total', 'click_date');

        $series = [];

        for ($offset = 0; $offset < $days; $offset++) {
            $date = $from->copy()->addDays($offset)->toDateString();
            $series[$date] = (int) ($totals[$date] ?? 0);
        }

        return $series;
    }

    /**
     * Orígenes (cabecera Referer) con más clics.
     *
     * @return array<string, int> Referer => total de clics
     */
    public function topReferers(int $limit = 5): array
    {
        return $this->clicks()
            ->whereNotNull('referer')
            ->selectRaw('referer, COUNT(*) as total')
            ->groupBy('referer')
            ->orderByDesc('total')
            ->limit($limit)
            ->pluck('total', 'referer')
            ->map(fn ($total): int => (int) $total)
            ->all();
    }

    /**
     * Todas las cifras que muestra el panel de un enlace.
     *
     * @return array{total: int, last30: int, today: int, peak: int, daily: array<string, int>, referers: array<string, int>}
     */
    public function stats(): array
    {
        $daily = $this->clicksPerDay();

        return [
            'total' => $this->clicks()->count(),
            'last30' => array_sum($daily),
            'today' => (int) end($daily),
            'peak' => max(max($daily), 1),
            'daily' => $daily,
            'referers' => $this->topReferers(),
        ];
    }
}
