<?php

namespace TightenCo\Jigsaw\Providers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\View\ViewException;
use NunoMaduro\Collision\Contracts\Provider as CollisionProviderContract;
use NunoMaduro\Collision\Provider as CollisionProvider;
use Spatie\LaravelIgnition\Views\ViewExceptionMapper;
use TightenCo\Jigsaw\Support\ServiceProvider;

class ExceptionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /** @internal The '__testing' binding is for Jigsaw development only and may be removed. */
        if (! $this->app->has('__testing') || ! $this->app['__testing']) {
            $this->app->bind(CollisionProviderContract::class, fn () => new CollisionProvider);
        }

        $this->app->make(ExceptionHandler::class)->map(
            fn (ViewException $e) => $this->app->make(ViewExceptionMapper::class)->map($e),
        );
    }
}
