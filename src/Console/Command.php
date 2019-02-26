<?php

namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends SymfonyCommand
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->console = new ConsoleSession(
            $this->input,
            $this->output,
            $this->getHelper('question')
        );

        return (int) $this->fire();
    }

    abstract protected function fire();
}
