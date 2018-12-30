<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Handlers;

class IgnoredHandler
{
    public function shouldHandle($file)
    {
        return preg_match('/(^\/*_)/', $file->getRelativePathname()) === 1;
    }

    public function handle($file, $data)
    {
        return collect([]);
    }
}
