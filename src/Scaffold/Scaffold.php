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

    public function setBase($cwd = null)
    {
        $this->base = $cwd ?: getcwd();
    }

    abstract public function build($preset);
}
