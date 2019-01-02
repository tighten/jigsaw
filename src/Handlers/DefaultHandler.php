<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Handlers;

use Illuminate\Support\Collection;
use TightenCo\Jigsaw\Contracts\ItemHandler;
use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\File\CopyFile;
use TightenCo\Jigsaw\File\InputFile;
use TightenCo\Jigsaw\PageData;

class DefaultHandler implements ItemHandler
{
    /** @var Filesystem */
    private $files;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    public function shouldHandle(InputFile $file): bool
    {
        return true;
    }

    public function handle(InputFile $file, PageData $pageData): Collection
    {
        return collect([
            new CopyFile(
                $file->getPathName(),
                $file->getRelativePath(),
                $file->getBasename('.' . $file->getExtension()),
                $file->getExtension(),
                $pageData
            ),
        ]);
    }
}
