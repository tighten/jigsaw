<?php namespace Jigsaw\Jigsaw\Handlers;

use Illuminate\Contracts\View\Factory;
use Jigsaw\Jigsaw\Filesystem;
use Jigsaw\Jigsaw\ProcessedFile;

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
