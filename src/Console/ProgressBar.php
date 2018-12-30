<?php

declare(strict_types=1);

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

    public function getMessage(): ?string
    {
        return $this->message ? '<comment>' . $this->message . '</comment>' : null;
    }

    public function start(): ProgressBar
    {
        if ($this->consoleOutput->isVerbose()) {
            $this->progressBar->setFormat('normal');
            $this->progressBar->start();
        }

        return $this;
    }

    public function addSteps($count): ProgressBar
    {
        $this->progressBar->setMaxSteps($this->progressBar->getMaxSteps() + $count);

        return $this;
    }

    public function advance(): ProgressBar
    {
        $this->progressBar->advance();

        return $this;
    }
}
