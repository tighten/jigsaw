<?php namespace Jigsaw\Jigsaw;

use Illuminate\Contracts\View\Factory;
use Illuminate\Filesystem\Filesystem;

class DefaultHandler
{
    private $files;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    public function canHandle($file)
    {
        return true;
    }

    public function handle($file)
    {
        return new ProcessedFile($file->getRelativePathname(), $this->files->get($file->getRealPath()));
    }
}
