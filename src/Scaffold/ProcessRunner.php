<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Scaffold;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use JsonSerializable;
use Symfony\Component\Process\Process;
use Traversable;

class ProcessRunner
{
    /**
     * @param array|Collection|Arrayable|Jsonable|JsonSerializable|Traversable $commands
     */
    public function run($commands = []): ProcessRunner
    {
        collect($commands)->each(function (string $command): void {
            $this->runCommand($command);
        });

        if ($commands) {
            echo "\n";
        }

        return $this;
    }

    protected function runCommand(string $command): ProcessRunner
    {
        echo "\n> " . $command . "\n";
        $process = new Process($command);
        $process->setTty(true)->run();

        if (! $process->isSuccessful()) {
            throw new InstallerCommandException($command);
        }

        return $this;
    }
}
