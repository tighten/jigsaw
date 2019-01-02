<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Contracts;

interface ProgressBar
{
    public function getMessage(): ?string;

    public function start(): ProgressBar;

    public function addSteps(int $count): ProgressBar;

    public function advance(): ProgressBar;
}
