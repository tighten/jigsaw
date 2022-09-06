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

        $this->registerCoreAliases();
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

    public function cachePath(string ...$path): string
    {
        return $this->basePath('cache', ...$path);
    }

    public function bootstrap(array $bootstrappers): void
    {
        if (! $this->bootstrapped) {
            $this->bootstrapped = true;

            $this->loadEnvironmentVariables();

            $this->loadConfiguration();

            $this->registerConfiguredProviders();

            $this->boot();
        }
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

    public function loadConfiguration(): void
    {
        $config = collect();

        $files = array_filter([
            $this->basePath('config.php'),
            $this->basePath('helpers.php'),
        ], 'file_exists');

        foreach ($files as $path) {
            $config = $config->merge(require $path);
        }

        if ($config->get('collections')) {
            $config->put('collections', collect($config->get('collections'))->flatMap(
                fn ($value, $key) => is_array($value) ? [$key => $value] : [$value => []]
            ));
        }

        $this->instance('cachePath', $this->cachePath());
        $this->instance('buildPath', [
            'source' => $this->basePath('source'),
            'destination' => $this->basePath('build_{env}'),
        ]);

        $config->put('view.compiled', $this->cachePath());

        $this->instance('config', $config);

        mb_internal_encoding('UTF-8');
    }

    public function registerConfiguredProviders(): void
    {
        foreach ([
            Providers\EventServiceProvider::class,
            Providers\FilesystemServiceProvider::class,
            Providers\MarkdownServiceProvider::class,
            Providers\ViewServiceProvider::class,
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
            'view' => [\Illuminate\View\Factory::class, \Illuminate\Contracts\View\Factory::class],
        ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }
}
