<?php

require_once dirname(__DIR__, 2).'/bootstrap/application-path.php';

afterEach(function (): void {
    if (isset($this->layoutRoot) && is_string($this->layoutRoot)) {
        deleteTestDirectory($this->layoutRoot);
    }
});

it('resolves laravel from a sys directory next to the document root', function (): void {
    $root = makeApplicationLayout();
    $this->layoutRoot = $root;
    $publicPath = $root.DIRECTORY_SEPARATOR.'public_html';

    expect(resolve_application_path($publicPath))->toBe(realpath($publicPath.DIRECTORY_SEPARATOR.'sys'));
});

it('throws when sys/vendor is missing', function (): void {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'open9-empty-'.uniqid();
    mkdir($root);
    $this->layoutRoot = $root;

    resolve_application_path($root);
})->throws(RuntimeException::class);

function makeApplicationLayout(): string
{
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'open9-layout-'.uniqid();
    $vendor = $root.DIRECTORY_SEPARATOR.'public_html'.DIRECTORY_SEPARATOR.'sys'.DIRECTORY_SEPARATOR.'vendor';
    mkdir($vendor, 0777, true);
    file_put_contents($vendor.DIRECTORY_SEPARATOR.'autoload.php', '<?php');

    return $root;
}

function deleteTestDirectory(string $directory): void
{
    if (! is_dir($directory)) {
        return;
    }

    $items = scandir($directory);

    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $directory.DIRECTORY_SEPARATOR.$item;

        if (is_dir($path)) {
            deleteTestDirectory($path);

            continue;
        }

        unlink($path);
    }

    rmdir($directory);
}
