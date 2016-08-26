<?php namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\Filesystem;
use TightenCo\Jigsaw\OutputFile;

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
        return [
            new OutputFile(
                $file->getRelativePath(),
                $file->getBasename('.'.$file->getExtension()),
                $file->getExtension(),
                $this->files->get($file->getRealPath()),
                $data
            )
        ];
    }
}
