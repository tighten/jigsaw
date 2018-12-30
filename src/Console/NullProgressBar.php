<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Console;

class NullProgressBar
{
    protected $consoleOutput;
    protected $message;

    public function __construct(ConsoleOutput $consoleOutput, $message = null, $section = null)
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
