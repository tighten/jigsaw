<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Output\ConsoleOutput as SymfonyConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

class ConsoleOutput extends SymfonyConsoleOutput
{
    /** @var ProgressBar[] */// TODO use interface instead of class
    protected $progressBars;

    /** @var ConsoleSectionOutput[] */
    protected $sections;

    public function setup($verbosity): void
    {
        $this->setVerbosity($verbosity);
        $this->setupSections();
        $this->setupProgressBars();
    }

    protected function setupSections(): void
    {
        $this->sections = collect([
            'footer' => $this->section(),
            'intro' => $this->section(),
            'message' => $this->section(),
            'progress' => $this->section(),
            'header' => $this->section(),
        ])->map(function ($section): ConsoleSectionOutput {
            return $this->section();
        });

        $this->sections['header']->writeln('');
        $this->sections['footer']->writeln('');
    }

    protected function setupProgressBars(): void
    {
        $this->progressBars = [
            'collections' => $this->getProgressBar('Loading collections...'),
            'build' => $this->getProgressBar('Building files from source...'),
        ];
    }

    protected function getProgressBar($message = null): ProgressBar // TODO use interface of class
    {
        return $this->isVerbose() ?
            new ProgressBar($this, $message, $this->sections['progress']) :
            new NullProgressBar($this, $message, $this->sections['progress']);
    }

    public function progressBar($name): ?ProgressBar // TODO use interface of class
    {
        return $this->progressBars[$name];
    }

    public function startProgressBar($name, $steps = null): void
    {
        $this->sections['progress']->clear();
        $progressBar = $this->progressBar($name);

        if ($progressBar->getMessage()) {
            $this->sections['message']->overwrite($progressBar->getMessage());
        }

        $progressBar->addSteps($steps)->start();
    }

    public function writeIntro($env, $useCache = false, $cacheExisted = false): ConsoleOutput
    {
        if ($useCache) {
            if ($cacheExisted) {
                $cacheMessage = '(using cache)';
            } else {
                $cacheMessage = '(creating cache)';
            }
        } else {
            $cacheMessage = '';
        }

        $this->sections['intro']->overwrite(
            '<fg=green>Building '
            . $env
            . ' site '
            . $cacheMessage
            . '</>'
        );

        return $this;
    }

    public function writeWritingFiles(): ConsoleOutput
    {
        $this->sections['progress']->clear();
        $this->sections['message']->overwrite('<fg=yellow>Writing files to destination...</>');

        return $this;
    }

    public function writeTime($time, $useCache = false, $cacheExisted = false): ConsoleOutput
    {
        if ($useCache) {
            if ($cacheExisted) {
                $cacheMessage = '(using cache)';
            } else {
                $cacheMessage = '(cache was created)';
            }
        } else {
            $cacheMessage = '';
        }

        $this->sections['intro']->overwrite(
            '<fg=yellow>Build time: </><fg=white>' .
            $time .
            ' seconds</> ' .
            $cacheMessage
        );

        return $this;
    }

    public function writeConclusion(): ConsoleOutput
    {
        $this->sections['message']->overwrite('<fg=green>Site build successfully!</>');

        return $this;
    }
}
