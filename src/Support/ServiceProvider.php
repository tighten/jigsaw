<?php

namespace TightenCo\Jigsaw\Support;

use TightenCo\Jigsaw\Container;

abstract class ServiceProvider
{
    public function __construct(
        protected Container $app,
    ) {
    }

    public function register(): void
    {
        //
    }
}
