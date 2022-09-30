<?php

namespace TightenCo\Jigsaw\Providers;

use TightenCo\Jigsaw\Support\ServiceProvider;

class BootstrapFileServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (file_exists($bootstrap = $this->app->path('bootstrap.php'))) {
            $events = $this->app->events;
            $container = $this->app;
            include $bootstrap;
        }
    }
}
