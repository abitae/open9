<section class="space-y-4">
    <div>
        <flux:heading size="xl">Acceso con Google</flux:heading>
        <flux:text class="text-xs">Permite que los clientes se registren e inicien sesión con Google (OAuth). El secreto se guarda cifrado.</flux:text>
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <form wire:submit="save" class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
        <flux:checkbox wire:model="form.google_enabled" label="Habilitar acceso con Google en la tienda" />

        <div class="grid gap-3 md:grid-cols-2">
            <flux:input wire:model="form.google_client_id" label="Google Client ID" placeholder="1234567890-abc.apps.googleusercontent.com" />
            <flux:input wire:model="google_client_secret" label="Google Client Secret (vacío = no cambiar)" type="password" placeholder="GOCSPX-..." />
        </div>

        <flux:input wire:model="form.google_redirect_url" label="URL de redirección autorizada" placeholder="{{ url('/api/auth/google/callback') }}" />
        <flux:text class="text-xs text-zinc-500">
            Registra esta URL en Google Cloud Console → Credenciales → URIs de redirección autorizados:
            <code>{{ url('/api/auth/google/callback') }}</code>
        </flux:text>
        <flux:text class="text-xs text-zinc-500">
            Tras el login, el backend redirige al frontend que inició el flujo (<code>return_to</code>).
            Si no se envía, usa <code>FRONTEND_URL</code> del servidor (actualmente
            <code>{{ rtrim((string) config('app.frontend_url'), '/') ?: '—' }}</code>).
        </flux:text>
        <flux:callout variant="warning" class="text-xs">
            Si el dominio público (p. ej. <code>open9.dev</code>) sirve el build estático de Vite,
            debe devolver <code>index.html</code> en rutas como <code>/auth/callback</code> e
            <code>/ingresar</code>. Sin ese fallback SPA, Google OAuth termina en error 404.
            Usa <code>try_files</code> (Caddy/Nginx), <code>_redirects</code> o <code>404.html</code>
            del build — ver <code>frontend2_open9/public/Caddyfile.example</code>.
        </flux:callout>

        <flux:button type="submit" variant="primary">Guardar</flux:button>
    </form>

    <div class="space-y-2 rounded-lg border border-zinc-200 p-4 text-xs text-zinc-500 dark:border-zinc-700">
        <flux:heading size="sm">Cómo obtener las credenciales</flux:heading>
        <ol class="list-decimal space-y-1 pl-4">
            <li>Entra a Google Cloud Console y crea (o selecciona) un proyecto.</li>
            <li>Ve a «APIs y servicios» → «Credenciales» → «Crear credenciales» → «ID de cliente de OAuth».</li>
            <li>Tipo de aplicación: «Aplicación web». Agrega la URI de redirección de arriba.</li>
            <li>Copia el Client ID y el Client Secret y pégalos aquí.</li>
        </ol>
    </div>
</section>
