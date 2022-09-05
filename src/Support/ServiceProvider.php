<?php

namespace TightenCo\Jigsaw\Support;

use TightenCo\Jigsaw\Container;

abstract class ServiceProvider
{
    protected Container $app;

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
