<?php

namespace TightenCo\Jigsaw\Console;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ServeCommand extends Command
{
    private $app;

    public function __construct(Container $app)
    {
        $this->app = $app;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('serve')
            ->setDescription('Serve local site with php built-in server.')
            ->addArgument(
                'environment',
                InputArgument::OPTIONAL,
                'What environment should we serve?',
                'local'
            )
            ->addOption(
                'host',
                null,
                InputOption::VALUE_OPTIONAL,
                'What hostname or ip address should we use?',
                'localhost'
            )
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_REQUIRED,
                'What port should we use?',
                8000
            )
            ->addOption(
                'no-build',
                null,
                InputOption::VALUE_NONE,
                'Skip build before serving?'
            );
    }

    protected function fire()
    {
        $env = $this->input->getArgument('environment');
        $host = $this->input->getOption('host');
        $port = $this->input->getOption('port');

        if (! $this->input->getOption('no-build')) {
            $buildCmd = $this->getApplication()->find('build');
            $buildArgs = new ArrayInput(['env' => $env]);
            $buildCmd->run($buildArgs, $this->output);
        }

        $this->console->info("Server started on http://{$host}:{$port}");

        passthru("php -S {$host}:{$port} -t " . escapeshellarg($this->getBuildPath($env)));
    }

    private function getBuildPath($env)
    {
        $environmentConfigPath = $this->getAbsolutePath("config.{$env}.php");
        $environmentConfig = file_exists($environmentConfigPath) ? include $environmentConfigPath : [];

        $customBuildPath = Arr::get(
            $environmentConfig,
            'build.destination',
            Arr::get($this->app->config, 'build.destination')
        );

        $buildPath = $customBuildPath ? $this->getAbsolutePath($customBuildPath) : $this->app->buildPath['destination'];

        return str_replace('{env}', $env, $buildPath);
    }

    private function getAbsolutePath($path)
    {
        return $this->app->cwd . '/' . trimPath($path);
    }
}
