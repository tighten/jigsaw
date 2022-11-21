<?php

namespace TightenCo\Jigsaw\Console;

use Illuminate\Console\Concerns\HasParameters;
use Illuminate\Console\Parser;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends SymfonyCommand
{
    use HasParameters;

    /** @var string */
    protected $signature;

    protected $input;
    protected $output;
    protected $console;

    public function __construct()
    {
        if (isset($this->signature)) {
            $this->parseSignature();
        } else {
            parent::__construct($this->name);

            $this->specifyParameters();
        }

        $this->setDescription((string) $this->description);
        $this->setHelp((string) $this->help);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->console = new ConsoleSession(
            $this->input,
            $this->output,
            $this->getHelper('question'),
        );

        return (int) $this->fire();
    }

    abstract protected function fire();

    private function parseSignature(): void
    {
        [$name, $arguments, $options] = Parser::parse($this->signature);

        parent::__construct($this->name = $name);

        $this->getDefinition()->addArguments($arguments);
        $this->getDefinition()->addOptions($options);
    }
}
