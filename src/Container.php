<?php

namespace TightenCo\Jigsaw;

use Closure;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Illuminate\Container\Container as Illuminate;
use Illuminate\Support\Env;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class Container extends Illuminate
{
    protected string $basePath;

    private bool $bootstrapped = false;
    private array $providers = [];

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);

        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance('cwd', getcwd());
    }

    public function basePath(...$path): string
    {
        return implode(DIRECTORY_SEPARATOR, array_filter([$this->basePath, ...$path]));
    }

    public function contentPath(...$path): string
    {
        return $this->basePath('content', ...$path);
    }

    public function publicPath(...$path): string
    {
        return $this->basePath('public', ...$path);
    }

    public function bootstrap(array $bootstrappers): void
    {
        if (! $this->bootstrapped) {
            $this->bootstrapped = true;

            $this->loadEnvironmentVariables();
            $this->registerConfiguredProviders();

            $this->boot();
        }
    }

    public function detectEnvironment(Closure $callback): string
    {
        return $this['env'] = ($input = new ArgvInput)->hasParameterOption('--env')
            ? $input->getParameterOption('--env')
            : $callback();
    }

    public function loadEnvironmentVariables(): void
    {
        try {
            Dotenv::create(Env::getRepository(), $this->basePath)->safeLoad();
        } catch (InvalidFileException $e) {
            $output = (new ConsoleOutput)->getErrorOutput();

            $output->writeln('The environment file is invalid!');
            $output->writeln($e->getMessage());

            die(1);
        }
    }

    public function registerConfiguredProviders(): void
    {
        foreach ([
            Providers\EventServiceProvider::class,
            Providers\FilesystemServiceProvider::class,
            Providers\MarkdownServiceProvider::class,
        ] as $provider) {
            ($provider = new $provider($this))->register();

            $this->providers[] = $provider;
        }
    }

    public function boot(): void
    {
        array_walk($this->providers, function ($provider) {
            $this->call([$provider, 'boot']);
        });
    }

    protected function registerCoreAliases(): void
    {
        foreach ([
            'app' => [static::class, \Illuminate\Contracts\Container\Container::class],
        ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }
}
