<?php

namespace TightenCo\Jigsaw\Scaffold;

use TightenCo\Jigsaw\File\Filesystem;

class BasicScaffold extends Scaffold
{
    public function build($preset = null)
    {
        $this->scaffoldSite();
        $this->scaffoldMix();
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
