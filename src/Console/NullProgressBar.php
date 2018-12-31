<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Output\ConsoleSectionOutput;

class NullProgressBar
{
    /** @var ConsoleOutput */
    protected $consoleOutput;

    /** @var ?string */
    protected $message;

    public function __construct(ConsoleOutput $consoleOutput, ?string $message = null, ?ConsoleSectionOutput $section = null)
    {
        $this->consoleOutput = $consoleOutput;
        $this->message = $message;

        if ($section) {
            $section->writeln('');
        }
    }

    public function getMessage(): ?string
    {
        return $this->message ? '<comment>' . $this->message . '</comment>' : null;
    }

    public function start(): NullProgressBar
    {
        return $this;
    }

    public function addSteps($count): NullProgressBar
    {
        return $this;
    }

    public function advance(): NullProgressBar
    {
        return $this;
    }
}
