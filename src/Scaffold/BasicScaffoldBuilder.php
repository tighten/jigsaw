<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Scaffold;

class BasicScaffoldBuilder extends ScaffoldBuilder
{
    public function init($preset = null, $question = null): ScaffoldBuilder
    {
        return $this;
    }

    public function build(): ScaffoldBuilder
    {
        $this->scaffoldSite();
        $this->scaffoldMix();

        return $this;
    }

    protected function scaffoldSite(): void
    {
        $this->files->copyDirectory(__DIR__ . '/../../stubs/site', $this->base);
    }

    protected function scaffoldMix(): void
    {
        $this->files->copyDirectory(__DIR__ . '/../../stubs/mix', $this->base);
    }
}
