<?php namespace TightenCo\Jigsaw\Console;

use TightenCo\Jigsaw\Jigsaw;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends Command
{
    private $sourcePath;
    private $buildPath;
    private $jigsaw;

    public function __construct($jigsaw, $sourcePath, $buildPath)
    {
        $this->sourcePath = $sourcePath;
        $this->buildPath = $buildPath;
        $this->jigsaw = $jigsaw;
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

        $config = $this->loadConfig();
        $collections = $this->loadCollections();

        $this->buildPath .= '_' . $env;

        if ($this->input->getOption('pretty') === 'false') {
            $this->jigsaw->setOption('pretty', false);
        }

        $this->jigsaw->build($this->sourcePath, $this->buildPath, $config, $collections);
        $this->info('Site built successfully!');
    }

    private function loadConfig()
    {
        $env = $this->input->getArgument('env');

        if ($env !== null && file_exists(getcwd() . "/config.{$env}.php")) {
            $environmentConfig = include getcwd() . "/config.{$env}.php";
        } else {
            $environmentConfig = [];
        }

        return array_merge(include getcwd() . '/config.php', $environmentConfig);
    }

    private function loadCollections()
    {
        if (! file_exists(getcwd() . '/collections.php')) {
            return [];
        }

        return include getcwd() . '/collections.php';
    }
}
