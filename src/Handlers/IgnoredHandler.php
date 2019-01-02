<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Handlers;

use Illuminate\Support\Collection;
use TightenCo\Jigsaw\Contracts\ItemHandler;
use TightenCo\Jigsaw\File\InputFile;
use TightenCo\Jigsaw\PageData;

class IgnoredHandler implements ItemHandler
{
    public function shouldHandle(InputFile $file): bool
    {
        return preg_match('/(^\/*_)/', $file->getRelativePathname()) === 1;
    }

    public function handle(InputFile $file, PageData $data): Collection
    {
        return collect([]);
    }
}
