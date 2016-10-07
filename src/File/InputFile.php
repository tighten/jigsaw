<?php namespace TightenCo\Jigsaw\File;

class InputFile
{
    protected $file;
    protected $basePath;

    public function __construct($file, $basePath)
    {
        $this->file = $file;
        $this->basePath = $basePath;
    }

    public function topLevelDirectory()
    {
        $parts = explode('/', $this->relativePath());

        return count($parts) == 1 ? '' : $parts[0];
    }

    public function relativePath()
    {
        return str_replace($this->basePath . '/', '', $this->file->getPathname());
    }

    public function bladeViewPath()
    {
        return $this->getRelativePath() . '/' . $this->getFilenameWithoutExtension();
    }

    public function getFilenameWithoutExtension()
    {
        return $this->getBasename('.' . $this->getFullExtension());
    }

    public function getFullExtension()
    {
        $extension = $this->getExtension();

        return strpos($this->getBasename(), '.blade.' . $extension) > 0 ? 'blade.' . $extension : $extension;
    }

    public function __call($method, $args)
    {
        return $this->file->{$method}(...$args);
    }
}
