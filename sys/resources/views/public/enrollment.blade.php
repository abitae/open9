<x-layouts::public>
    <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-xs font-semibold tracking-widest text-brand-dark uppercase">Inscripción</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight">{{ $course->title }}</h1>
        <p class="mt-2 text-sm leading-relaxed text-zinc-500">
            Completa tus datos y te contactaremos para confirmar el cupo y el pago.
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

        <form method="POST" action="{{ route('courses.enrollment.store', $course->slug) }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="full_name" class="mb-1.5 block text-sm font-medium">Nombre completo</label>
                <input id="full_name" name="full_name" value="{{ old('full_name') }}" required autocomplete="name" class="w-full rounded-xl border border-brand-gray bg-white px-3 py-2.5 text-sm outline-none transition focus:border-accent focus:ring-2 focus:ring-accent/20">
                @error('full_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="document_type" class="mb-1.5 block text-sm font-medium">Tipo de documento</label>
                    <input id="document_type" name="document_type" value="{{ old('document_type') }}" class="w-full rounded-xl border border-brand-gray bg-white px-3 py-2.5 text-sm outline-none transition focus:border-accent focus:ring-2 focus:ring-accent/20">
                </div>
                <div>
                    <label for="document_number" class="mb-1.5 block text-sm font-medium">Número de documento</label>
                    <input id="document_number" name="document_number" value="{{ old('document_number') }}" class="w-full rounded-xl border border-brand-gray bg-white px-3 py-2.5 text-sm outline-none transition focus:border-accent focus:ring-2 focus:ring-accent/20">
                </div>
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
                    <label for="city" class="mb-1.5 block text-sm font-medium">Ciudad</label>
                    <input id="city" name="city" value="{{ old('city') }}" autocomplete="address-level2" class="w-full rounded-xl border border-brand-gray bg-white px-3 py-2.5 text-sm outline-none transition focus:border-accent focus:ring-2 focus:ring-accent/20">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="occupation" class="mb-1.5 block text-sm font-medium">Ocupación</label>
                    <input id="occupation" name="occupation" value="{{ old('occupation') }}" class="w-full rounded-xl border border-brand-gray bg-white px-3 py-2.5 text-sm outline-none transition focus:border-accent focus:ring-2 focus:ring-accent/20">
                </div>
                <div>
                    <label for="company" class="mb-1.5 block text-sm font-medium">Empresa</label>
                    <input id="company" name="company" value="{{ old('company') }}" autocomplete="organization" class="w-full rounded-xl border border-brand-gray bg-white px-3 py-2.5 text-sm outline-none transition focus:border-accent focus:ring-2 focus:ring-accent/20">
                </div>
            </div>

            <div>
                <label for="notes" class="mb-1.5 block text-sm font-medium">Notas</label>
                <textarea id="notes" name="notes" rows="4" class="min-h-24 w-full rounded-xl border border-brand-gray bg-white px-3 py-2.5 text-sm outline-none transition focus:border-accent focus:ring-2 focus:ring-accent/20">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-accent px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-brand-dark">
                Enviar inscripción
            </button>
        </form>
    </div>
</x-layouts::public>
