<?php namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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

    private function getBuildPath($env)
    {
        $environmentConfigPath = $this->getAbsolutePath("config.{$env}.php");
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
        return $this->app->cwd . '/' . trimPath($path);
    }
}
