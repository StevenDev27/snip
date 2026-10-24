<x-layouts.app>
    <section class="space-y-8">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">Acorta un enlace</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Pega una URL larga, comparte el enlace corto y mira cuántos clics recibe cada día.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('links.store') }}"
            class="space-y-4 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900"
        >
            @csrf

            <div>
                <label for="original_url" class="block text-sm font-medium">URL original</label>
                <input
                    type="url"
                    id="original_url"
                    name="original_url"
                    value="{{ old('original_url') }}"
                    placeholder="https://ejemplo.com/una/url/muy/larga"
                    required
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:outline-none dark:border-gray-700 dark:bg-gray-950"
                >
                @error('original_url')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                <div class="sm:w-56">
                    <label for="expires_at" class="block text-sm font-medium">
                        Caduca el <span class="font-normal text-gray-500">(opcional)</span>
                    </label>
                    <input
                        type="date"
                        id="expires_at"
                        name="expires_at"
                        value="{{ old('expires_at') }}"
                        min="{{ now()->addDay()->toDateString() }}"
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:outline-none dark:border-gray-700 dark:bg-gray-950"
                    >
                    @error('expires_at')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-indigo-500 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none"
                >
                    Acortar
                </button>
            </div>
        </form>

        <div class="space-y-3">
            <h2 class="text-lg font-semibold">Enlaces recientes</h2>

            @forelse ($links as $link)
                <a
                    href="{{ route('links.show', $link) }}"
                    class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4 transition hover:border-indigo-400 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500"
                >
                    <div class="min-w-0">
                        <p class="font-medium text-indigo-600 dark:text-indigo-400">{{ $link->shortUrl() }}</p>
                        <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ $link->original_url }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-xl font-semibold tabular-nums">{{ $link->clicks_count }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">clics</p>
                    </div>
                </a>
            @empty
                <p class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    Todavía no has creado ningún enlace.
                </p>
            @endforelse

            {{ $links->links() }}
        </div>
    </section>
</x-layouts.app>
