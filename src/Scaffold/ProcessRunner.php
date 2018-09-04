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

        return $this;
    }

    protected function runCommand($command)
    {
        $process = new Process($command);
        echo("\n> " . $command . "\n");
        $process->setTty(true);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if (! $process->isSuccessful()) {
            throw new InstallerCommandException($command);
        }

        return $this;
    }
}
