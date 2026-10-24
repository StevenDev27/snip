<?php

use App\Models\Click;
use App\Models\Link;

describe('index', function () {
    it('lists the visitor links with their click count', function () {
        $link = Link::factory()->ownedBy('token-a')->has(Click::factory()->count(3))->create();

        $this->withCookie('snip_owner', 'token-a')
            ->get(route('links.index'))
            ->assertOk()
            ->assertSee($link->shortUrl())
            ->assertViewHas('links', fn ($links) => $links->first()->clicks_count === 3);
    });

    it('hides links that belong to other visitors', function () {
        $other = Link::factory()->ownedBy('token-b')->create();

        $this->withCookie('snip_owner', 'token-a')
            ->get(route('links.index'))
            ->assertOk()
            ->assertDontSee($other->shortUrl());
    });

    it('lists the demo links for visitors without a cookie', function () {
        $demo = Link::factory()->create();

        $this->get(route('links.index'))
            ->assertOk()
            ->assertSee($demo->shortUrl());
    });

    it('shows an empty state when there are no links', function () {
        $this->get(route('links.index'))
            ->assertOk()
            ->assertSee('Todavía no has creado ningún enlace.');
    });
});

describe('store', function () {
    it('creates a link and redirects to its stats page', function () {
        $response = $this->post(route('links.store'), [
            'original_url' => 'https://laravel.com/docs',
        ]);

        $link = Link::sole();

        $response->assertRedirect(route('links.show', $link));
        expect($link->original_url)->toBe('https://laravel.com/docs')
            ->and($link->code)->toHaveLength(6)
            ->and($link->expires_at)->toBeNull();
    });

    it('issues an owner cookie to first-time visitors', function () {
        $this->post(route('links.store'), ['original_url' => 'https://laravel.com/docs'])
            ->assertCookie('snip_owner');

        expect(Link::sole()->owner_token)->not->toBeNull();
    });

    it('assigns the link to the visitor identified by the cookie', function () {
        $this->withCookie('snip_owner', 'token-a')
            ->post(route('links.store'), ['original_url' => 'https://laravel.com/docs']);

        expect(Link::sole()->owner_token)->toBe('token-a');
    });

    it('stores the expiration at the end of the chosen day', function () {
        $day = now()->addDays(3);

        $this->post(route('links.store'), [
            'original_url' => 'https://laravel.com/docs',
            'expires_at' => $day->toDateString(),
        ]);

        expect(Link::sole()->expires_at->toDateTimeString())
            ->toBe($day->endOfDay()->toDateTimeString());
    });

    it('requires the url', function () {
        $this->post(route('links.store'), [])
            ->assertSessionHasErrors(['original_url' => 'Escribe la URL que quieres acortar.']);
    });

    it('rejects urls that are not http or https', function (string $url) {
        $this->post(route('links.store'), ['original_url' => $url])
            ->assertSessionHasErrors('original_url');

        expect(Link::count())->toBe(0);
    })->with([
        'javascript scheme' => 'javascript:alert(1)',
        'ftp scheme' => 'ftp://example.com/file',
        'no scheme' => 'example.com',
    ]);

    it('rejects an expiration date that is not in the future', function () {
        $this->post(route('links.store'), [
            'original_url' => 'https://laravel.com/docs',
            'expires_at' => now()->toDateString(),
        ])->assertSessionHasErrors(['expires_at' => 'La fecha de caducidad debe ser posterior a hoy.']);

        expect(Link::count())->toBe(0);
    });
});

describe('show', function () {
    it('shows totals and the top referers', function () {
        $link = Link::factory()->create();
        Click::factory()->for($link)->from('https://google.com')->count(2)->create();
        Click::factory()->for($link)->from('https://x.com')->create();

        $this->get(route('links.show', $link))
            ->assertOk()
            ->assertSee('https://google.com')
            ->assertViewHas('stats', fn (array $stats) => $stats['total'] === 3
                && $stats['today'] === 3
                && array_key_first($stats['referers']) === 'https://google.com');
    });

    it('groups clicks by day and fills the days without clicks', function () {
        $link = Link::factory()->create();
        Click::factory()->for($link)->daysAgo(2)->count(2)->create();
        Click::factory()->for($link)->create();

        $this->get(route('links.show', $link))
            ->assertViewHas('stats', function (array $stats) {
                return count($stats['daily']) === 30
                    && $stats['daily'][now()->subDays(2)->toDateString()] === 2
                    && $stats['daily'][now()->subDay()->toDateString()] === 0
                    && $stats['daily'][now()->toDateString()] === 1;
            });
    });

    it('keeps clicks older than 30 days out of the daily series', function () {
        $link = Link::factory()->create();
        Click::factory()->for($link)->daysAgo(45)->create();

        $this->get(route('links.show', $link))
            ->assertViewHas('stats', fn (array $stats) => $stats['total'] === 1 && $stats['last30'] === 0);
    });

    it('serves cached stats for a minute and refreshes them afterwards', function () {
        $link = Link::factory()->create();
        Click::factory()->for($link)->create();

        $this->get(route('links.show', $link));
        Click::factory()->for($link)->create();

        $this->get(route('links.show', $link))
            ->assertViewHas('stats', fn (array $stats) => $stats['total'] === 1);

        $this->travel(61)->seconds();

        $this->get(route('links.show', $link))
            ->assertViewHas('stats', fn (array $stats) => $stats['total'] === 2);
    });

    it('returns 404 when the code does not exist', function () {
        $this->get(route('links.show', 'nope00'))->assertNotFound();
    });
});
