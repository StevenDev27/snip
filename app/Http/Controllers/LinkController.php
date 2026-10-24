<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLinkRequest;
use App\Models\Link;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function index(): View
    {
        return view('links.index', [
            'links' => Link::query()->withCount('clicks')->latest('id')->paginate(10),
        ]);
    }

    public function store(StoreLinkRequest $request): RedirectResponse
    {
        $link = Link::create([
            'code' => Link::generateCode(),
            'original_url' => $request->validated('original_url'),
            'expires_at' => $request->date('expires_at')?->endOfDay(),
        ]);

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
