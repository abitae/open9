<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-brand-ink antialiased">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:rounded-full focus:bg-accent focus:px-4 focus:py-2 focus:text-sm focus:text-white">
            Saltar al contenido
        </a>

        <header class="border-b border-brand-gray bg-white/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-xl items-center justify-between gap-4 px-4 py-4">
                <a href="{{ config('app.frontend_url', '/') }}" class="flex items-center">
                    <x-brand-logo variant="light" class="h-8" />
                </a>
                <a href="{{ config('app.frontend_url', '/') }}" class="text-sm text-zinc-500 transition hover:text-brand-dark">
                    Volver al sitio
                </a>
            </div>
        </header>

        <main id="main-content" class="mx-auto max-w-xl px-4 py-10">
            {{ $slot }}
        </main>
    </body>
</html>
