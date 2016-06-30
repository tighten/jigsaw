<?php namespace TightenCo\Jigsaw;

class ProcessedCollectionFile
{
    private $processedFile;
    private $settings;

    public function __construct($processedFile, $settings)
    {
        $this->processedFile = $processedFile;
        $this->settings = $settings;
    }

    private function permalinkPath()
    {
        return $this->settings['permalink']->__invoke($this->processedFile->data());
    }

    public function relativePathname()
    {
        return $this->permalinkPath() . '.' . $this->extension();
    }

    public function basename()
    {
        return collect(explode('/', $this->permalinkPath()))->last();
    }

    public function relativePath()
    {
        return collect(explode('/', $this->permalinkPath()))->slice(0, -1)->implode('/');
    }

    public function prettyDirectory()
    {
        return "{$this->relativePath()}/{$this->basename()}";
    }

    public function prettyRelativePathname()
    {
        return $this->prettyDirectory() . '/index.html';
    }

    public function __call($method, $args)
    {
        return $this->processedFile->{$method}(...$args);
    }
}
