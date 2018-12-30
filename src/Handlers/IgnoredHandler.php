<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Handlers;

use Illuminate\Support\Collection;

class IgnoredHandler
{
    public function shouldHandle($file): bool
    {
        return preg_match('/(^\/*_)/', $file->getRelativePathname()) === 1;
    }

    public function handle($file, $data): Collection
    {
        return collect([]);
    }
}
