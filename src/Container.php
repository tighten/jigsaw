<?php

namespace TightenCo\Jigsaw;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Illuminate\Container\Container as Illuminate;
use Illuminate\Support\Env;
use Symfony\Component\Console\Output\ConsoleOutput;

class Container extends Illuminate
{
    private string $path;

    private bool $bootstrapped = false;

    private bool $booted = false;

    /** @var callable[] */
    private array $bootingCallbacks = [];

    /** @var callable[] */
    private array $bootedCallbacks = [];

    private array $providers = [];

    public function __construct()
    {
        $this->path = getcwd();

        static::setInstance($this);
        $this->instance('app', $this);

        $this->registerCoreProviders();
        $this->registerCoreAliases();
    }

    public function bootstrapWith(array $bootstrappers): void
    {
        $this->bootstrapped = true;

        $this->loadEnvironmentVariables();
        $this->loadConfiguration();

        foreach ($bootstrappers as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }

        $this->registerConfiguredProviders();
        $this->boot();
    }

    public function path(string ...$path): string
    {
        return implode('/', array_filter([$this->path, ...$path]));
    }

    public function cachePath(string ...$path): string
    {
        return $this->path('cache', ...$path);
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }

    public function booting(callable $callback): void
    {
        $this->bootingCallbacks[] = $callback;
    }

    public function booted(callable $callback): void
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
            $callback($this);
        }
    }

    private function loadEnvironmentVariables(): void
    {
        try {
            Dotenv::create(Env::getRepository(), $this->path)->safeLoad();
        } catch (InvalidFileException $e) {
            $output = (new ConsoleOutput)->getErrorOutput();

            $output->writeln('The environment file is invalid!');
            $output->writeln($e->getMessage());

            exit(1);
        }
    }

    private function loadConfiguration(): void
    {
        $config = collect();

        $files = array_filter([
            $this->path('config.php'),
            $this->path('helpers.php'),
        ], 'file_exists');

        foreach ($files as $path) {
            $config = $config->merge(require $path);
        }

        if ($config->get('collections')) {
            $config->put('collections', collect($config->get('collections'))->flatMap(
                fn ($value, $key) => is_array($value) ? [$key => $value] : [$value => []],
            ));
        }

        $this->instance('buildPath', [
            'source' => $this->path('source'),
            'destination' => $this->path('build_{env}'),
        ]);

        $config->put('view.compiled', $this->cachePath());

        $this->instance('config', $config);

        setlocale(LC_ALL, 'en_US.UTF8');
    }

    private function boot(): void
    {
        $this->fireAppCallbacks($this->bootingCallbacks);

        array_walk($this->providers, function ($provider) {
            if (method_exists($provider, 'boot')) {
                $this->call([$provider, 'boot']);
            }
        });

        $this->booted = true;

        $this->fireAppCallbacks($this->bootedCallbacks);
    }

    /** @param callable[] $callbacks */
    private function fireAppCallbacks(array &$callbacks): void
    {
        $index = 0;

        while ($index < count($callbacks)) {
            $callbacks[$index]($this);

            ++$index;
        }
    }

    private function registerCoreProviders(): void
    {
        foreach ([
            Providers\EventServiceProvider::class,
        ] as $provider) {
            ($provider = new $provider($this))->register();

            $this->providers[] = $provider;
        }
    }

    private function registerConfiguredProviders(): void
    {
        foreach ([
            Providers\ExceptionServiceProvider::class,
            Providers\FilesystemServiceProvider::class,
            Providers\MarkdownServiceProvider::class,
            Providers\ViewServiceProvider::class,
            Providers\CollectionServiceProvider::class,
            Providers\CompatibilityServiceProvider::class,
            Providers\BootstrapFileServiceProvider::class,
        ] as $provider) {
            ($provider = new $provider($this))->register();

            $this->providers[] = $provider;
        }
    }

    private function registerCoreAliases(): void
    {
        foreach ([
            'app' => [self::class, \Illuminate\Contracts\Container\Container::class, \Psr\Container\ContainerInterface::class],
            'view' => [\Illuminate\View\Factory::class, \Illuminate\Contracts\View\Factory::class],
        ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }
}
