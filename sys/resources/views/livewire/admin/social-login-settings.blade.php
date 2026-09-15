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

        <flux:input :value="$liveRedirectUri" label="URI que envía este host ahora" disabled />
        <flux:text class="text-xs text-zinc-500">
            Laravel arma el callback con el dominio de esta petición. Guardar aquí no lo registra en Google.
        </flux:text>

        <flux:callout variant="warning" icon="exclamation-triangle">
            En Cloud Console → Credenciales → ID de cliente OAuth pega estas URIs (https, sin barra final, sin <code>/sys</code>).
            Si falta la del host desde el que entra el cliente, Google responde 400 redirect_uri_mismatch.
        </flux:callout>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
                <flux:heading size="sm">URIs de redirección autorizados</flux:heading>
                <ul class="space-y-1 text-xs">
                    @foreach ($consoleRedirectUris as $uri)
                        <li><code class="break-all">{{ $uri }}</code></li>
                    @endforeach
                </ul>
            </div>
            <div class="space-y-2">
                <flux:heading size="sm">Orígenes de JavaScript autorizados</flux:heading>
                <ul class="space-y-1 text-xs">
                    @foreach ($consoleJavaScriptOrigins as $origin)
                        <li><code class="break-all">{{ $origin }}</code></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <flux:button type="submit" variant="primary">Guardar</flux:button>
    </form>

    <div class="space-y-2 rounded-lg border border-zinc-200 p-4 text-xs text-zinc-500 dark:border-zinc-700">
        <flux:heading size="sm">Cómo obtener las credenciales</flux:heading>
        <ol class="list-decimal space-y-1 pl-4">
            <li>Entra a Google Cloud Console y crea (o selecciona) un proyecto.</li>
            <li>Ve a «APIs y servicios» → «Credenciales» → «Crear credenciales» → «ID de cliente de OAuth».</li>
            <li>Tipo de aplicación: «Aplicación web». Agrega todas las URIs de redirección y orígenes de JavaScript de arriba.</li>
            <li>Copia el Client ID y el Client Secret y pégalos aquí.</li>
            <li>Espera 1–5 minutos y prueba «Continuar con Google» en el mismo dominio que fallaba.</li>
        </ol>
    </div>
</section>
