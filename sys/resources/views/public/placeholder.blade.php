<x-layouts::public>
    <div class="rounded-3xl border border-zinc-200 bg-white p-6 text-center shadow-sm sm:p-10">
        <p class="text-xs font-semibold tracking-widest text-brand-dark uppercase">Certificados OPEN9</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight">{{ $title }}</h1>
        <p class="mx-auto mt-3 max-w-sm text-sm leading-relaxed text-zinc-500">
            Esta verificación estará disponible cuando el certificado se emita desde el panel de academia.
        </p>
        <a href="{{ config('app.frontend_url', '/') }}" class="mt-6 inline-flex rounded-full bg-accent px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-dark">
            Ir al sitio
        </a>
    </div>
</x-layouts::public>
