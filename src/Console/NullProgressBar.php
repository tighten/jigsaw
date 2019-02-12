<?php

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

    public function getMessage()
    {
        return $this->message ? '<comment>' . $this->message . '</comment>' : null;
    }

    public function start()
    {
        return $this;
    }

    public function addSteps($count)
    {
        return $this;
    }

    public function advance()
    {
        return $this;
    }
}
