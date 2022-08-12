<?php

namespace TightenCo\Jigsaw\Scaffold;

class BasicScaffoldBuilder extends ScaffoldBuilder
{
    public function init($preset = null)
    {
        return $this;
    }

    public function build()
    {
        $this->files->copyDirectory(__DIR__ . '/../../stubs/site', $this->base);

        return $this;
    }
}
