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
        $parts = explode(DIRECTORY_SEPARATOR, $this->getRelativeFilePath());

        return count($parts) == 1 ? '' : $parts[0];
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

    public function getRelativeFilePath()
    {
        $relative_path = str_replace(realpath($this->basePath), '', realpath($this->file->getPathname()));

        return trimPath($relative_path);
    }

    public function __call($method, $args)
    {
        return $this->file->{$method}(...$args);
    }
}
