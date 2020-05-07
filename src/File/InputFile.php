<?php

namespace TightenCo\Jigsaw\File;

use Illuminate\Support\Str;
use TightenCo\Jigsaw\PageData;

class InputFile
{
    protected $file;
    protected $extraBladeExtensions = [
        'js', 'json', 'xml', 'yaml', 'yml', 'rss', 'atom', 'txt', 'text', 'html',
    ];
    protected $pageData;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function setPageData(PageData $pageData)
    {
        $this->pageData = $pageData->page;
    }

    public function getPageData()
    {
        return $this->pageData;
    }

    public function getFileInfo()
    {
        return $this->file;
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
        if (! Str::startsWith($this->getFilename(), '.')) {
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

    public function getLastModifiedTime()
    {
        return $this->file->getMTime();
    }

    public function isBladeFile()
    {
        return strpos($this->getBasename(), '.blade.' . $this->getExtension()) > 0;
    }

    public function __call($method, $args)
    {
        return $this->file->{$method}(...$args);
    }
}
