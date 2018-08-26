<?php

namespace TightenCo\Jigsaw\File;

class InputFile
{
    protected $file;
    protected $extraBladeExtensions = [
        'js', 'json', 'xml', 'rss', 'atom', 'txt', 'text', 'html',
    ];

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function topLevelDirectory()
    {
        $parts = explode(DIRECTORY_SEPARATOR, $this->file->getRelativePathName());

        return count($parts) == 1 ? '' : $parts[0];
    }

    public function getFilenameWithoutExtension()
    {
        return $this->getBasename('.' . $this->getFullExtension());
    }

    public function getExtension()
    {
        if (! starts_with($this->getFilename(), '.')) {
            return $this->file->getExtension();
        }
    }

    public function getFullExtension()
    {
        return $this->isBladeFile() ? 'blade.' . $this->getExtension() : $this->getExtension();
    }

    public function getExtraBladeExtension()
    {
        return $this->isBladeFile() && in_array($this->getExtension(), $this->extraBladeExtensions) ? $this->getExtension() : '';
    }

    protected function isBladeFile()
    {
        return strpos($this->getBasename(), '.blade.' . $this->getExtension()) > 0;
    }

    public function __call($method, $args)
    {
        return $this->file->{$method}(...$args);
    }
}
