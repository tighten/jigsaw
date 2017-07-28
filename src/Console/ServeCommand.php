<?php namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use TightenCo\Jigsaw\ConfigFile;

class ServeCommand extends Command
{
    private $app;

    public function __construct($app)
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
                'site',
                's',
                InputOption::VALUE_OPTIONAL,
                'What site should we use?',
                null
            )
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_REQUIRED,
                'What port should we use?',
                8000
            );
    }

    protected function fire()
    {
        $env = $this->input->getArgument('environment');
        $port = $this->input->getOption('port');

        $this->info("Server started on http://localhost:{$port}");

        passthru("php -S localhost:{$port} -t " . escapeshellarg($this->getBuildPath($env)));
    }

    private function getSiteConfig($env)
    {
        if(!isset($this->app->config['sites'])) {
            return $this->getAbsolutePath("config.{$env}.php");
        }

        $site = $this->input->getOption('site');
        if(empty($site)) {
            $site = key($this->app->config['sites']);
        }

        $config = $this->app->config['sites'][ $site ];
        return $this->getAbsolutePath($config.DIRECTORY_SEPARATOR."config.php");
    }

    private function getBuildPath($env)
    {
        $environmentConfigPath = $this->getSiteConfig($env);
        $environmentConfig = file_exists($environmentConfigPath) ? include $environmentConfigPath : [];

        $customBuildPath = array_get(
            $environmentConfig,
            'build.destination',
            array_get($this->app->config, 'build.destination')
        );

        $buildPath = $customBuildPath ? $this->getAbsolutePath($customBuildPath) : $this->app->buildPath['destination'];

        return str_replace('{env}', $env, $buildPath);
    }

    private function getAbsolutePath($path)
    {
        return $this->app->cwd . '/' . trim($path, '/');
    }
}
