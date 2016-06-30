<?php namespace TightenCo\Jigsaw;

class CollectionDataLoader
{
    private $settings;
    private $filesystem;
    private $handlers;

    public function __construct($settings, $filesystem, $handlers)
    {
        $this->settings = collect($settings);
        $this->filesystem = $filesystem;
        $this->handlers = collect($handlers);
    }

    public function load($source)
    {
        return $this->settings->map(function ($settings, $name) use ($source) {
            return $this->loadSingleCollectionData($source, $name, $settings);
        })->all();
    }

    private function loadSingleCollectionData($source, $collectionName, $settings)
    {
        return collect($this->filesystem->allFiles("{$source}/_{$collectionName}"))->map(function ($file) use ($settings) {
            return $this->buildCollectionItem($file, $settings);
        })->all();
    }

    private function buildCollectionItem($file, $settings)
    {
        $handler = $this->handlers->first(function ($_, $handler) use ($file) {
            return $handler->shouldHandle($file);
        }, function () { throw new Exception('No matching collection item handler'); });

        return $handler->buildCollectionItem($file, array_get($settings, 'helpers', []));
    }
}
