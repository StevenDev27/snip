<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLinkRequest;
use App\Models\Link;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LinkController extends Controller
{
    private const OWNER_COOKIE = 'snip_owner';

    public function index(Request $request): View
    {
        return view('links.index', [
            'links' => Link::query()
                ->visibleTo($request->cookie(self::OWNER_COOKIE))
                ->withCount('clicks')
                ->latest('id')
                ->paginate(10),
        ]);
    }

    public function store(StoreLinkRequest $request): RedirectResponse
    {
        $ownerToken = $request->cookie(self::OWNER_COOKIE) ?? (string) Str::uuid();

        $link = Link::create([
            'code' => Link::generateCode(),
            'original_url' => $request->validated('original_url'),
            'expires_at' => $request->date('expires_at')?->endOfDay(),
            'owner_token' => $ownerToken,
        ]);

        Cookie::queue(Cookie::forever(self::OWNER_COOKIE, $ownerToken));

        return redirect()->route('links.show', $link);
    }

    public function show(Link $link): View
    {
        $stats = Cache::remember("links.{$link->id}.stats", 60, fn (): array => $link->stats());

        return view('links.show', [
            'link' => $link,
            'stats' => $stats,
        ]);
    }
}
