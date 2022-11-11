<?php

namespace TightenCo\Jigsaw\Providers;

use Illuminate\Events\Dispatcher;
use TightenCo\Jigsaw\Container;
use TightenCo\Jigsaw\Events\EventBus;
use TightenCo\Jigsaw\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('dispatcher', fn (Container $app) => new Dispatcher($app));

        $this->app->singleton('events', fn (Container $app) => new EventBus);
    }
}
