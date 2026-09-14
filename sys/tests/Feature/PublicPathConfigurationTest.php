<?php

use App\Providers\AppServiceProvider;

it('uses the parent of sys as the public path by default', function () {
    expect(public_path())->toBe(dirname(base_path()));
});

it('applies the configured public path when the directory exists', function () {
    $original = public_path();
    $custom = sys_get_temp_dir().DIRECTORY_SEPARATOR.'open9-public-'.uniqid();
    mkdir($custom);

    try {
        config(['app.public_path' => $custom]);

        (new AppServiceProvider(app()))->register();

        expect(app()->publicPath())->toBe($custom);
    } finally {
        app()->usePublicPath($original);
        config(['app.public_path' => null]);
        rmdir($custom);
    }
});
