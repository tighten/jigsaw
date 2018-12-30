<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends SymfonyCommand
{
    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->input = $input;
        $this->output = $output;
        $this->console = new ConsoleSession(
            $this->input,
            $this->output,
            $this->getHelper('question')
        );

        $this->fire();
        return 0;
    }

    abstract protected function fire(): void;
}
