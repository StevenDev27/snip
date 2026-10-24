@use('Illuminate\Support\Carbon')

<x-layouts.app :title="$link->code">
    <a href="{{ route('links.index') }}" class="text-sm text-gray-500 transition hover:text-indigo-500 dark:text-gray-400">
        ← Volver
    </a>

    <div class="mt-4 space-y-6">
        <section class="space-y-3 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap items-center gap-3">
                <p class="text-2xl font-semibold text-indigo-600 dark:text-indigo-400">{{ $link->shortUrl() }}</p>

                <button
                    type="button"
                    data-copy="{{ $link->shortUrl() }}"
                    onclick="navigator.clipboard.writeText(this.dataset.copy); this.textContent = '¡Copiado!'"
                    class="rounded-lg border border-gray-300 px-3 py-1 text-sm transition hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800"
                >
                    Copiar
                </button>

                @if ($link->expires_at?->isPast())
                    <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-300">
                        Expirado
                    </span>
                @elseif ($link->expires_at)
                    <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                        Caduca el {{ $link->expires_at->format('d/m/Y') }}
                    </span>
                @endif
            </div>

            <p class="text-sm break-all text-gray-500 dark:text-gray-400">
                Destino:
                <a href="{{ $link->original_url }}" target="_blank" rel="noopener noreferrer nofollow" class="underline hover:text-indigo-500">
                    {{ $link->original_url }}
                </a>
            </p>
        </section>

        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">Clics totales</p>
                <p class="mt-1 text-4xl font-semibold tabular-nums">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">Últimos 30 días</p>
                <p class="mt-1 text-4xl font-semibold tabular-nums">{{ $stats['last30'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">Hoy</p>
                <p class="mt-1 text-4xl font-semibold tabular-nums">{{ $stats['today'] }}</p>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold">Clics por día</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Últimos 30 días</p>

            <div class="mt-6 flex h-40 items-end gap-1" role="img" aria-label="Gráfico de barras con los clics de cada día">
                @foreach ($stats['daily'] as $day => $total)
                    <div class="flex h-full flex-1 items-end" title="{{ Carbon::parse($day)->format('d/m') }}: {{ $total }} clics">
                        <div
                            @class([
                                'w-full rounded-t-sm',
                                'bg-indigo-500' => $total > 0,
                                'bg-gray-200 dark:bg-gray-800' => $total === 0,
                            ])
                            style="height: {{ $total > 0 ? max(round($total / $stats['peak'] * 100), 4) : 2 }}%"
                        ></div>
                    </div>
                @endforeach
            </div>

            <div class="mt-2 flex justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>{{ Carbon::parse(array_key_first($stats['daily']))->format('d/m') }}</span>
                <span>Hoy</span>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold">Principales orígenes</h2>

            @forelse ($stats['referers'] as $referer => $total)
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2 text-sm last:border-b-0 dark:border-gray-800">
                    <span class="truncate">{{ $referer }}</span>
                    <span class="font-medium tabular-nums">{{ $total }}</span>
                </div>
            @empty
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Todavía no hay datos de origen.</p>
            @endforelse
        </section>
    </div>
</x-layouts.app>
