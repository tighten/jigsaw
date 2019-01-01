<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Helper\ProgressBar as SymfonyProgressBar;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

class ProgressBar
{
    /** @var ConsoleOutput */
    protected $consoleOutput;

    /** @var SymfonyProgressBar */
    protected $progressBar;

    /** @var ?string */
    protected $message;

    public function __construct(ConsoleOutput $consoleOutput, ?string $message = null, ?ConsoleSectionOutput $section = null)
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

    public function addSteps(int $count): ProgressBar
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
