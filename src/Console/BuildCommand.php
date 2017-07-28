<?php namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use TightenCo\Jigsaw\ConfigFile;
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
            ->addOption('site', 's', InputOption::VALUE_OPTIONAL, "Build a specific site.", null)
            ->addOption('pretty', null, InputOption::VALUE_REQUIRED, "Should the site use pretty URLs?", 'true');
    }

    protected function fire()
    {
        $env = $this->input->getArgument('env');
        $this->includeEnvironmentConfig($env);

        if ($this->input->getOption('pretty') === 'true') {
            $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        }

        if(!$this->app->config->has('sites')) {
            $this->buildSite($env);
            return;
        }

        $site = $this->input->getOption('site');

        if(!empty($site)) {
            if(!array_key_exists($site, $this->app->config['sites'])) {
                $this->error("The site {$site} was not found in the config file's sites array.");
                return;
            }

            $this->buildSite($env, $site, $this->app->config['sites'][$site]);
            return;
        }

        $sites = $this->app->config['sites'];
        foreach($sites as $name => $source) {
            $this->buildSite($env, $name, $source);
        }
    }

    private function buildSite($env, $site = null, $source = null)
    {
        if(!empty($site)) {
            $config = (new ConfigFile($this->getAbsolutePath($source.DIRECTORY_SEPARATOR.'config.php')))->config;
            $config['build']['source'] = $source.DIRECTORY_SEPARATOR.'source';
            $this->app->instance('config', collect($config));
        } else {
            $site = 'Site';
        }

        $this->updateBuildPaths($env);
        $this->app->make(Jigsaw::class)->build($env);
        $this->info("{$site} built successfully.");
    }

    private function includeEnvironmentConfig($env)
    {
        $environmentConfigPath = $this->getAbsolutePath("config.{$env}.php");
        $environmentConfig = file_exists($environmentConfigPath) ? include $environmentConfigPath : [];

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
        return $this->app->cwd . '/' . trim($path, '/');
    }
}
