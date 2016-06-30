<?php namespace TightenCo\Jigsaw\Handlers;

use Illuminate\Contracts\View\Factory;
use TightenCo\Jigsaw\Filesystem;
use TightenCo\Jigsaw\ProcessedFile;

class DefaultHandler
{
    private $files;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    public function shouldHandle($file)
    {
        return true;
    }

    public function handle($file, $data)
    {
        return [new ProcessedFile($file->getFilename(), $file->getRelativePath(), $this->files->get($file->getRealPath()), $data)];
    }
}
