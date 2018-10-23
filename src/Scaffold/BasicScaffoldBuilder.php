<?php

namespace TightenCo\Jigsaw\Scaffold;

class BasicScaffoldBuilder extends ScaffoldBuilder
{
    public function init($preset = null, $question = null)
    {
        return $this;
    }

    public function build()
    {
        $this->scaffoldSite();
        $this->scaffoldMix();

        return $this;
    }

    protected function scaffoldSite()
    {
        $this->files->copyDirectory(__DIR__ . '/../../stubs/site', $this->base);
    }

    protected function scaffoldMix()
    {
        $this->files->copyDirectory(__DIR__ . '/../../stubs/mix', $this->base);
    }
}
