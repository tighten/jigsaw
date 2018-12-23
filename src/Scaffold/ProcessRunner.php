<?php

namespace TightenCo\Jigsaw\Scaffold;

use Symfony\Component\Process\Process;
use TightenCo\Jigsaw\Scaffold\InstallerCommandException;

class ProcessRunner
{
    public function run($commands = [])
    {
        collect($commands)->each(function ($command) {
            $this->runCommand($command);
        });

        if ($commands) {
            echo("\n");
        }

        return $this;
    }

    protected function runCommand($command)
    {
        echo("\n> " . $command . "\n");
        $process = new Process($command);
        $process->setTty(true)->run();

        if (! $process->isSuccessful()) {
            throw new InstallerCommandException($command);
        }

        return $this;
    }
}
