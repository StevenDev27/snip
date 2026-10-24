@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · Snip' : 'Snip · Acortador de enlaces' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
    <header class="border-b border-gray-200 dark:border-gray-800">
        <div class="mx-auto flex max-w-4xl items-center justify-between gap-4 px-4 py-4">
            <a href="{{ route('links.index') }}" class="text-lg font-semibold tracking-tight">
                snip<span class="text-indigo-500">.</span>
            </a>
            <span class="text-sm text-gray-500 dark:text-gray-400">Enlaces cortos con estadísticas</span>
        </div>
    </header>

    <main class="mx-auto max-w-4xl px-4 py-10">
        {{ $slot }}
    </main>
</body>
</html>
