<?php namespace TightenCo\Jigsaw\File;

class InputFile
{
    protected $file;
    protected $basePath;
    protected $extraBladeExtensions = [
        'js', 'json', 'xml', 'rss', 'atom', 'txt', 'text', 'html'
    ];

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

    public function getRelativeFilePath()
    {
        $relative_path = str_replace(resolvePath($this->basePath), '', resolvePath($this->file->getPathname()));

        return trimPath($relative_path);
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
