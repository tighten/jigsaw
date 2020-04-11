<?php

namespace TightenCo\Jigsaw\Scaffold;

use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class ProcessRunner
{
    public function run($commands = [])
    {
        collect($commands)->each(function ($command) {
            $this->runCommand($command);
        });

        if ($commands) {
            echo "\n";
        }

        return $this;
    }

    protected function runCommand($command)
    {
        echo "\n> " . $command . "\n";
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(3600);
        $process->setIdleTimeout(120);

        try {
            $process->setTty(true)->run();
        } catch (RuntimeException $e) {
            $process->run(function ($type, $buffer) {
                echo $buffer;
            });
        }

        if (! $process->isSuccessful()) {
            throw new InstallerCommandException($command);
        }

        return $this;
    }
}
