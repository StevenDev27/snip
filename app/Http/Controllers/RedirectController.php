<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RedirectController extends Controller
{
    /**
     * Registra el clic y redirige al destino original.
     */
    public function __invoke(Request $request, string $code): RedirectResponse
    {
        $link = Link::where('code', $code)->firstOrFail();

        abort_if($link->expires_at?->isPast(), 410);

        $link->clicks()->create([
            'clicked_at' => now(),
            'referer' => $this->truncated($request->headers->get('referer')),
            'user_agent' => $this->truncated($request->userAgent()),
        ]);

        return redirect()->away($link->original_url, 302);
    }

    private function truncated(?string $value): ?string
    {
        return filled($value) ? Str::limit($value, 250, '') : null;
    }
}
