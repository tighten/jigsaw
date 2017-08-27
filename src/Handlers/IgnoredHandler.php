<?php namespace TightenCo\Jigsaw\Handlers;

class IgnoredHandler
{
    public function shouldHandle($file)
    {
        return preg_match('/(^(_|\.DS_Store)|\/(_|\.DS_Store))/', $file->getRelativePathname()) === 1;
    }

    public function handle($file, $data)
    {
        return [];
    }
}
