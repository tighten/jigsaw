<?php

namespace TightenCo\Jigsaw\Providers;

use TightenCo\Jigsaw\Console\ConsoleOutput;
use TightenCo\Jigsaw\Support\ServiceProvider;

class CompatibilityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->instance('cwd', $this->app->path());

        $this->app->singleton('consoleOutput', fn () => new ConsoleOutput);
    }
}
