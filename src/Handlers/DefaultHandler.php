<?php

namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\File\CopyFile;
use TightenCo\Jigsaw\File\Filesystem;

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

    public function handle($file, $pageData)
    {
        return collect([
            new CopyFile(
                $file,
                $file->getPathName(),
                $file->getRelativePath(),
                $file->getBasename('.' . $file->getExtension()),
                $file->getExtension(),
                $pageData
            ),
        ]);
    }
}
