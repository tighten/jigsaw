<?php

namespace TightenCo\Jigsaw\Scaffold;

use TightenCo\Jigsaw\File\Filesystem;

abstract class Scaffold
{
    public $base;
    protected $files;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        $this->setBase();
    }

    abstract public function init($preset);

    abstract public function build();

    public function setBase($cwd = null)
    {
        $this->base = $cwd ?: getcwd();

        return $this;
    }

    public function archiveExistingSite()
    {
        //
    }

    public function deleteExistingSite()
    {
        //
    }
}
