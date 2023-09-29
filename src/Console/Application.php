<?php

namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Application as Symfony;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TightenCo\Jigsaw\Container;

class Application extends Symfony
{
    private OutputInterface $lastOutput;

    public function __construct(
        private Container $app,
        string $version,
    ) {
        parent::__construct('Jigsaw', $version);

        // $this->setAutoExit(false);
        $this->setCatchExceptions(false);
    }

    /**
     * Run a command by name.
     *
     * @throws \Symfony\Component\Console\Exception\CommandNotFoundException
     */
    public function call(string $command, array $parameters = [], ?OutputInterface $outputBuffer = null): int
    {
        [$command, $input] = $this->parseCommand($command, $parameters);

        if (! $this->has($command)) {
            throw new CommandNotFoundException("The command \"{$command}\" does not exist.");
        }

        return $this->run($input, $this->lastOutput = $outputBuffer ?: new BufferedOutput);
    }

    /**
     * Get the output of the last run command.
     */
    public function output(): string
    {
        return isset($this->lastOutput) && method_exists($this->lastOutput, 'fetch')
            ? $this->lastOutput->fetch()
            : '';
    }

    /**
     * Add a command.
     */
    public function add(SymfonyCommand $command): ?SymfonyCommand
    {
        if ($command instanceof Command) {
            $command->setApp($this->app);
        }

        return parent::add($command);
    }

    /**
     * Parse a command and its input.
     */
    private function parseCommand(string $command, array $parameters): array
    {
        if (is_subclass_of($command, SymfonyCommand::class)) {
            $callingClass = true;

            $command = $this->app->make($command)->getName();
        }

        if (! isset($callingClass) && empty($parameters)) {
            $command = $this->getCommandName($input = new StringInput($command));
        } else {
            $input = new ArrayInput([$command, ...$parameters]);
        }

        return [$command, $input];
    }
}
