<?php

declare(strict_types=1);

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

    public function topLevelDirectory(): string
    {
        $parts = explode(DIRECTORY_SEPARATOR, $this->file->getRelativePathName());

        return count($parts) == 1 ? '' : $parts[0];
    }

    public function getFilenameWithoutExtension(): string
    {
        return $this->getBasename('.' . $this->getFullExtension());
    }

    public function getExtension(): ?string
    {
        if (! starts_with($this->getFilename(), '.')) {
            return $this->file->getExtension();
        }

        return null;
    }

    public function getFullExtension(): ?string
    {
        return $this->isBladeFile() ? 'blade.' . $this->getExtension() : $this->getExtension();
    }

    public function getExtraBladeExtension(): string
    {
        return $this->isBladeFile() && in_array($this->getExtension(), $this->extraBladeExtensions) ? $this->getExtension() : '';
    }

    public function getLastModifiedTime(): int
    {
        return $this->file->getMTime();
    }

    public function isBladeFile(): bool
    {
        return strpos($this->getBasename(), '.blade.' . $this->getExtension()) > 0;
    }

    /**
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->file->{$method}(...$args);
    }
}
