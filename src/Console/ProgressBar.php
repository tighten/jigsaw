<?php

namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Helper\ProgressBar as SymfonyProgressBar;

class ProgressBar
{
    protected $consoleOutput;
    protected $progressBar;
    protected $message;

    public function __construct(ConsoleOutput $consoleOutput, $message = null, $section = null)
    {
        $this->consoleOutput = $consoleOutput;
        $this->progressBar = new SymfonyProgressBar($section ?? $consoleOutput);
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message ? '<comment>' . $this->message . '</comment>' : null;
    }

    public function start()
    {
        if ($this->consoleOutput->isVerbose()) {
            $this->progressBar->setFormat('normal');
            $this->progressBar->start();
        }

        return $this;
    }

    public function addSteps($count)
    {
        $this->progressBar->setMaxSteps($this->progressBar->getMaxSteps() + $count);

        return $this;
    }

    public function advance()
    {
        $this->progressBar->advance();

        return $this;
    }
}
