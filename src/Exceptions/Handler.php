<?php

namespace TightenCo\Jigsaw\Exceptions;

use Illuminate\Console\View\Components\BulletList;
use Illuminate\Console\View\Components\Error;
use Illuminate\Contracts\Debug\ExceptionHandler;
use NunoMaduro\Collision\Adapters\Laravel\Inspector;
use NunoMaduro\Collision\Contracts\Provider;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\ExceptionInterface as SymfonyConsoleExceptionInterface;
use Throwable;

class Handler implements ExceptionHandler
{
    public function report(Throwable $e): void
    {
        //
    }

    public function render($request, Throwable $e): void
    {
        //
    }

    /**
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     */
    public function renderForConsole($output, Throwable $e): void
    {
        if ($e instanceof CommandNotFoundException) {
            $message = str($e->getMessage())->explode('.')->first();

            if (! empty($alternatives = $e->getAlternatives())) {
                $message .= '. Did you mean one of these?';

                with(new Error($output))->render($message);
                with(new BulletList($output))->render($e->getAlternatives());

                $output->writeln('');
            } else {
                with(new Error($output))->render($message);
            }

            return;
        }

        if ($e instanceof SymfonyConsoleExceptionInterface) {
            (new ConsoleApplication)->renderThrowable($e, $output);

            return;
        }

        /** @var \NunoMaduro\Collision\Contracts\Provider $provider */
        $provider = app(Provider::class);

        $handler = $provider->register()->getHandler()->setOutput($output);
        $handler->setInspector(new Inspector($e));

        $handler->handle();
    }

    public function shouldReport(Throwable $e): bool
    {
        return true;
    }
}
