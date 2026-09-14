<x-layouts::public>
    <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-xs font-semibold tracking-widest text-brand-dark uppercase">Contacto</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight">Hablemos de tu proyecto</h1>
        <p class="mt-2 text-sm leading-relaxed text-zinc-500">
            Cuéntanos qué necesitas. Te respondemos en menos de 24 horas.
        </p>

        @if (session('status'))
            <p class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                {{ session('status') }}
            </p>
        @endif

        @if ($errors->any())
            <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                Revisa los campos marcados e inténtalo de nuevo.
            </div>
        @endif

        <form method="POST" action="{{ route('contact.store') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium">Nombre</label>
                <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="w-full rounded-xl border border-brand-gray bg-white px-3 py-2.5 text-sm outline-none transition focus:border-accent focus:ring-2 focus:ring-accent/20">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium">Correo electrónico</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="w-full rounded-xl border border-brand-gray bg-white px-3 py-2.5 text-sm outline-none transition focus:border-accent focus:ring-2 focus:ring-accent/20">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-medium">Teléfono</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" class="w-full rounded-xl border border-brand-gray bg-white px-3 py-2.5 text-sm outline-none transition focus:border-accent focus:ring-2 focus:ring-accent/20">
                </div>
                <div>
                    <label for="subject" class="mb-1.5 block text-sm font-medium">Asunto</label>
                    <input id="subject" name="subject" value="{{ old('subject') }}" class="w-full rounded-xl border border-brand-gray bg-white px-3 py-2.5 text-sm outline-none transition focus:border-accent focus:ring-2 focus:ring-accent/20">
                </div>
            </div>

            <div>
                <label for="message" class="mb-1.5 block text-sm font-medium">Mensaje</label>
                <textarea id="message" name="message" required rows="5" class="min-h-32 w-full rounded-xl border border-brand-gray bg-white px-3 py-2.5 text-sm outline-none transition focus:border-accent focus:ring-2 focus:ring-accent/20">{{ old('message') }}</textarea>
                @error('message') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-accent px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-brand-dark">
                Enviar mensaje
            </button>
        </form>
    </div>
</x-layouts::public>
