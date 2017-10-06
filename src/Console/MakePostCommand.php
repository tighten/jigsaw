<?php

namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use TightenCo\Jigsaw\File\Filesystem;

class MakePostCommand extends Command
{
    private $files;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        parent::__construct();
    }

    protected function configure()
    {
    }

    protected function fire()
    {
    }

}
