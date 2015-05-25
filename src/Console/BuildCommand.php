<?php namespace Jigsaw\Jigsaw\Console;

use Illuminate\Contracts\View\Factory;
use Illuminate\Filesystem\Filesystem;
use Jigsaw\Jigsaw\Jigsaw;
use Jigsaw\Jigsaw\Template;
use Symfony\Component\Console\Input\InputInterface;
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
        $this->setName('build')->setDescription('Build your site.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        return (int) $this->fire();
    }

    protected function fire()
    {
        $this->jigsaw->build($this->sourcePath, $this->buildPath);
        $this->output->writeln('<info>Site built successfully!</info>');
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
