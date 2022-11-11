<?php

namespace TightenCo\Jigsaw\Providers;

use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\Support\ServiceProvider;

class FilesystemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('files', fn () => new Filesystem);
    }
}
