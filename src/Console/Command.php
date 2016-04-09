<?php namespace TightenCo\Jigsaw\Console;

use TightenCo\Jigsaw\Jigsaw;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends SymfonyCommand
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        return (int) $this->fire();
    }

    protected function info($string)
    {
        $this->output->writeln("<info>{$string}</info>");
    }

    protected function error($string)
    {
        $this->output->writeln("<error>{$string}</error>");
    }

    protected function comment($string)
    {
        $this->output->writeln("<comment>{$string}</comment>");
    }

    abstract protected function fire();
}
