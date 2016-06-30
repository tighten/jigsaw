<?php namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\ProcessedCollectionFile;

class CollectionItemHandler
{
    private $collectionSettings;
    private $handlers;

    public function __construct($collectionSettings, $handlers)
    {
        $this->collectionSettings = collect($collectionSettings);
        $this->handlers = collect($handlers);
    }

    public function shouldHandle($file)
    {
        return $this->isInCollectionDirectory($file);
    }

    private function isInCollectionDirectory($file)
    {
        $base = $file->topLevelDirectory();
        return starts_with($base, '_') && $this->hasCollectionNamed($this->getCollectionName($file));
    }

    private function hasCollectionNamed($candidate)
    {
        return $this->collectionSettings->has($candidate);
    }

    private function getCollectionName($file)
    {
        return substr($file->topLevelDirectory(), 1);
    }

    public function handle($file, $data)
    {
        $handler = $this->handlers->first(function ($_, $handler) use ($file) {
            return $handler->shouldHandle($file);
        });

        $processedFiles = $handler->handle($file, $data);
        $settings = $this->collectionSettings[$this->getCollectionName($file)];

        return collect($processedFiles)->map(function ($file) use ($settings) {
            return new ProcessedCollectionFile($file, $settings);
        })->all();
    }
}
