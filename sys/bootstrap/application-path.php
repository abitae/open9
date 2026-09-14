<?php

if (! function_exists('resolve_application_path')) {
    /**
     * Resolve the Laravel base path from the web document root.
     *
     * The application lives in a `sys/` directory next to `index.php`
     * (local repo root and BanaHosting `public_html`).
     *
     * @throws RuntimeException
     */
    function resolve_application_path(string $publicPath): string
    {
        $publicPath = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $publicPath), DIRECTORY_SEPARATOR);

        $applicationPath = $publicPath.DIRECTORY_SEPARATOR.'sys';
        $autoload = $applicationPath.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

        if (is_file($autoload)) {
            $resolved = realpath($applicationPath);

            return $resolved !== false ? $resolved : $applicationPath;
        }

        throw new RuntimeException(
            'No se encontró el autoload de Composer. La aplicación Laravel debe estar en sys/vendor relativo a la raíz web.',
        );
    }
}
