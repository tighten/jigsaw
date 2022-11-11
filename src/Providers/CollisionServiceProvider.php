<?php

namespace TightenCo\Jigsaw\Providers;

use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use NunoMaduro\Collision\Adapters\Laravel\ExceptionHandler;
use NunoMaduro\Collision\Contracts\Provider as ProviderContract;
use NunoMaduro\Collision\Handler;
use NunoMaduro\Collision\Provider;
use NunoMaduro\Collision\SolutionsRepositories\NullSolutionsRepository;
use NunoMaduro\Collision\Writer;
use TightenCo\Jigsaw\Support\ServiceProvider;

class CollisionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // TODO bind something global in the TestCase for now?
        // if (! $this->app->runningUnitTests()) {
            $this->app->bind(ProviderContract::class, function () {
                $writer = new Writer(new NullSolutionsRepository);
                $handler = new Handler($writer);

                return new Provider(null, $handler);
            });

            /** @var \Illuminate\Contracts\Debug\ExceptionHandler $appExceptionHandler */
            $appExceptionHandler = $this->app->make(ExceptionHandlerContract::class);

            $this->app->singleton(ExceptionHandlerContract::class, function ($app) use ($appExceptionHandler) {
                return new ExceptionHandler($app, $appExceptionHandler);
            });
        // }
    }
}
