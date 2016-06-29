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

    public function relativePathname()
    {
        return $this->settings['permalink']->__invoke($this->processedFile->data());
    }

    public function basename()
    {
        return collect(explode('/', $this->relativePathname()))->last();
    }

    public function relativePath()
    {
        return collect(explode('/', $this->relativePathname()))->slice(0, -1)->implode('/');
    }

    public function __call($method, $args)
    {
        return $this->processedFile->{$method}(...$args);
    }
}
