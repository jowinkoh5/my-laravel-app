@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ? $title.' · '.config('app.name') : config('app.name') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full bg-gray-50 font-sans text-gray-900 antialiased">
        <header class="border-b border-gray-200 bg-white">
            <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                <a href="{{ route('catalog.index') }}" class="text-base font-semibold tracking-tight">
                    {{ config('app.name') }}
                </a>

                <div class="flex items-center gap-4 text-sm">
                    <a href="{{ route('catalog.index') }}" class="text-gray-600 hover:text-gray-900">Catalogue</a>

                    @auth
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.products.index') }}" class="text-gray-600 hover:text-gray-900">
                                Dashboard
                            </a>
                        @endif

                        <a href="{{ route('docs.api') }}" class="text-gray-600 hover:text-gray-900">API docs</a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900">Log out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Log in</a>
                    @endauth
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
            @if (session('status'))
                <x-alert class="mb-6">{{ session('status') }}</x-alert>
            @endif

            {{ $slot }}
        </main>
    </body>
</html>
