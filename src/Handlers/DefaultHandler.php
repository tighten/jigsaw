<?php namespace Cambri\Jigsaw\Handlers;

use Illuminate\View\Factory;
use Illuminate\Filesystem\Filesystem;
use Cambri\Jigsaw\ProcessedFile;

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

    public function handle($file, $data)
    {
        return new ProcessedFile($file->getFilename(), $file->getRelativePath(), $this->files->get($file->getRealPath()));
    }
}
