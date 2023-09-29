<?php

namespace TightenCo\Jigsaw\Console;

use Illuminate\Console\Concerns\HasParameters;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\Concerns\InteractsWithSignals;
use Illuminate\Console\OutputStyle;
use Illuminate\Console\Parser;
use Illuminate\Console\View\Components\Factory;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TightenCo\Jigsaw\Container;

abstract class Command extends SymfonyCommand
{
    use HasParameters;
    use InteractsWithIO;
    use InteractsWithSignals;

    protected ?Container $app;

    // TODO could we type this as a string? it *will* break existing untyped $signature properties, does that matter?
    /** @var string */
    protected $signature;

    protected $input;
    protected $output;

    protected $console;

    // TODO previously, user-added commands received the container as an argument here. Will defining
    // this constructor break that in any way?
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

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $this->app->make(
            OutputStyle::class, ['input' => $input, 'output' => $output]
        );

        $this->components = $this->app->make(Factory::class, ['output' => $this->output]);

        try {
            return parent::run($this->input = $input, $this->output);
        } finally {
            $this->untrap();
        }
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

        $method = match (true) {
            method_exists($this, 'fire') => 'fire',
            method_exists($this, 'handle') => 'handle',
            default => '__invoke',
        };

        return (int) $this->app->call([$this, $method]);
    }

    public function setApp(Container $app): void
    {
        $this->app = $app;
    }

    private function parseSignature(): void
    {
        [$name, $arguments, $options] = Parser::parse($this->signature);

        parent::__construct($this->name = $name);

        $this->getDefinition()->addArguments($arguments);
        $this->getDefinition()->addOptions($options);
    }
}
