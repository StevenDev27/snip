<?php

use App\Models\Click;
use App\Models\Link;

it('redirects to the original url with a 302', function () {
    $link = Link::factory()->create(['original_url' => 'https://laravel.com/docs']);

    $this->get(route('links.redirect', $link->code))
        ->assertRedirect('https://laravel.com/docs')
        ->assertStatus(302);
});

it('records the click with its referer and user agent', function () {
    $link = Link::factory()->create();

    $this->withHeaders([
        'Referer' => 'https://news.ycombinator.com/',
        'User-Agent' => 'Pest/1.0',
    ])->get(route('links.redirect', $link->code));

    $click = $link->clicks()->sole();

    expect($click->referer)->toBe('https://news.ycombinator.com/')
        ->and($click->user_agent)->toBe('Pest/1.0');
});

it('stores a null referer when the request has none', function () {
    $link = Link::factory()->create();

    $this->get(route('links.redirect', $link->code));

    expect($link->clicks()->sole()->referer)->toBeNull();
});

it('returns 410 and records no click when the link has expired', function () {
    $link = Link::factory()->expired()->create();

    $this->get(route('links.redirect', $link->code))->assertStatus(410);

    expect(Click::count())->toBe(0);
});

it('returns 404 when the code does not exist', function () {
    $this->get('/zzzzzz')->assertNotFound();
});
