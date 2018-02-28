<?php namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use TightenCo\Jigsaw\File\ConfigFile;
use TightenCo\Jigsaw\Jigsaw;
use TightenCo\Jigsaw\PathResolvers\PrettyOutputPathResolver;

class BuildCommand extends Command
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('build')
            ->setDescription('Build your site.')
            ->addArgument('env', InputArgument::OPTIONAL, "What environment should we use to build?", 'local')
            ->addOption('pretty', null, InputOption::VALUE_REQUIRED, "Should the site use pretty URLs?", 'true');
    }

    protected function fire()
    {
        $env = $this->input->getArgument('env');
        $this->includeEnvironmentConfig($env);
        $this->updateBuildPaths($env);

        if ($this->input->getOption('pretty') === 'true') {
            $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        }

        $this->app->make(Jigsaw::class)->build($env);
        $this->info('Site built successfully!');
    }

    private function includeEnvironmentConfig($env)
    {
        $environmentConfigPath = $this->getAbsolutePath("config.{$env}.php");
        $environmentConfig = (new ConfigFile($environmentConfigPath))->config;

        $this->app->config = collect($this->app->config)
            ->merge(collect($environmentConfig))
            ->filter(function ($item) {
                return $item !== null;
            });
    }

    private function updateBuildPaths($env)
    {
        $this->app->buildPath = [
            'source' => $this->getBuildPath('source', $env),
            'destination' => $this->getBuildPath('destination', $env),
        ];
    }

    private function getBuildPath($pathType, $env)
    {
        $customPath = array_get($this->app->config, 'build.' . $pathType);
        $buildPath = $customPath ? $this->getAbsolutePath($customPath) : $this->app->buildPath[$pathType];

        return str_replace('{env}', $env, $buildPath);
    }

    private function getAbsolutePath($path)
    {
        return $this->app->cwd . '/' . trimPath($path);
    }
}
