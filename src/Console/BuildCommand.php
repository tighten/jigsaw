<?php namespace Jigsaw\Jigsaw\Console;

use Illuminate\Contracts\View\Factory;
use Illuminate\Filesystem\Filesystem;
use Jigsaw\Jigsaw\Jigsaw;
use Jigsaw\Jigsaw\Template;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends \Symfony\Component\Console\Command\Command
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
            ->addOption('env', null, InputOption::VALUE_REQUIRED, "What environment should we use to build?", 'local');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        return (int) $this->fire();
    }

    protected function fire()
    {
        $config = $this->loadConfig();
        $this->jigsaw->build($this->sourcePath, $this->buildPath, $config);
        $this->output->writeln('<info>Site built successfully!</info>');
    }

    private function loadConfig()
    {
        $env = $this->input->getOption('env');

        if ($env !== null && file_exists(getcwd() . "/config.{$env}.php")) {
            $environmentConfig = include getcwd() . "/config.{$env}.php";
        } else {
            $environmentConfig = [];
        }

        return array_merge(include getcwd() . '/config.php', $environmentConfig);
    }

    private function info($string)
    {
        $this->output->writeln("<info>{$string}</info>");
    }

    private function error($string)
    {
        $this->output->writeln("<error>{$string}</error>");
    }

    private function comment($string)
    {
        $this->output->writeln("<comment>{$string}</comment>");
    }
}
