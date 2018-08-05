<?php

namespace TightenCo\Jigsaw\Handlers;

class IgnoredHandler
{
    public function shouldHandle($file)
    {
        return 1 === preg_match('/(^\/*_)/', $file->getRelativePathname());
    }

    public function handle($file, $data)
    {
        return [];
    }
}
