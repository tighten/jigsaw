<?php namespace TightenCo\Jigsaw;

class InputFile
{
    private $file;
    private $basePath;

    public function __construct($file, $basePath)
    {
        $this->file = $file;
        $this->basePath = $basePath;
    }

    public function topLevelDirectory()
    {
        $parts = explode('/', $this->relativePath());

        if (count($parts) == 1) {
            return '';
        }

        return $parts[0];
    }

    public function relativePath()
    {
        return str_replace($this->basePath . '/', '', $this->file->getPathname());
    }

    public function __call($method, $args)
    {
        return $this->file->{$method}(...$args);
    }
}
