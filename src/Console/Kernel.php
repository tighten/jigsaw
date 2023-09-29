<?php

namespace TightenCo\Jigsaw\Console;

use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Throwable;
use TightenCo\Jigsaw\Container;
use TightenCo\Jigsaw\Jigsaw;

class Kernel implements KernelContract
{
    private ?Application $application = null;

    private array $bootstrappers = [
        \TightenCo\Jigsaw\Bootstrap\HandleExceptions::class,
    ];

    private array $commands = [
        \TightenCo\Jigsaw\Console\BuildCommand::class,
        \TightenCo\Jigsaw\Console\InitCommand::class,
        \TightenCo\Jigsaw\Console\ServeCommand::class,
    ];

    private bool $commandsLoaded = false;

    public function __construct(
        private Container $app,
    ) {}

    /**
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface|null  $output
     */
    public function handle($input, $output = null): int
    {
        try {
            $this->bootstrap();

            return $this->getApplication()->run($input, $output);
        } catch (Throwable $e) {
            $this->app[ExceptionHandler::class]->report($e);
            $this->app[ExceptionHandler::class]->renderForConsole($output, $e);

            return 1;
        }
    }

    /**
     * @param  string  $command
     * @param  array  $parameters
     * @param  \Symfony\Component\Console\Output\OutputInterface|null  $outputBuffer
     */
    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        $this->bootstrap();

        return $this->getApplication()->call($command, $parameters, $outputBuffer);
    }

    public function all(): array
    {
        $this->bootstrap();

        return $this->getApplication()->all();
    }

    /**
     * Get the output of the last run command.
     */
    public function output(): string
    {
        $this->bootstrap();

        return $this->getApplication()->output();
    }

    public function bootstrap(): void
    {
        if (! $this->app->isBootstrapped()) {
            $this->app->bootstrapWith($this->bootstrappers);
        }

        if (! $this->commandsLoaded) {
            // Load user commands

            $this->commandsLoaded = true;
        }
    }

    /**
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  int  $status
     */
    public function terminate($input, $status): void
    {
        // $this->app->terminate();
    }

    /**
     * Queue an Artisan console command by name.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    public function queue($command, array $parameters = [])
    {
        //
    }

    private function getApplication(): Application
    {
        if (is_null($this->application)) {
            $this->application = new Application($this->app, '1.6.0');

            foreach ($this->commands as $command) {
                $this->application->add($this->app->make($command));
            }

            Jigsaw::addUserCommands($this->application, $this->app);
        }

        return $this->application;
    }
}
