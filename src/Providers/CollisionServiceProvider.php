<?php

namespace TightenCo\Jigsaw\Providers;

use NunoMaduro\Collision\Contracts\Provider as ProviderContract;
use NunoMaduro\Collision\Contracts\Provider as CollisionProviderContract;
use NunoMaduro\Collision\Provider as CollisionProvider;
use TightenCo\Jigsaw\Support\ServiceProvider;

class CollisionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // TODO bind something global in the TestCase for now?
        // if (! $this->app->runningUnitTests()) {
        $this->app->bind(CollisionProviderContract::class, fn () => new CollisionProvider);
        // }
    }
}
