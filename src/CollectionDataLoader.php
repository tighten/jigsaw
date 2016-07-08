<?php namespace TightenCo\Jigsaw;

class CollectionDataLoader
{
    private $settings;
    private $filesystem;
    private $outputPathResolver;
    private $handlers;

    public function __construct($settings, $filesystem, $outputPathResolver, $handlers = [])
    {
        $this->settings = collect($settings);
        $this->filesystem = $filesystem;
        $this->outputPathResolver = $outputPathResolver;
        $this->handlers = collect($handlers);
    }

    public function load($source)
    {
        return $this->settings->map(function ($collectionSettings, $name) use ($source) {
            // Merge in any default collection settings
            $collectionSettings = array_merge([
                'helpers' => []
            ], $collectionSettings);

            return $this->loadSingleCollectionData($source, $name, $collectionSettings);
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

        $data = $handler->getData($file);
        $link = $this->getCollectionItemLink($data, $settings);

        return new CollectionItem(array_merge($data, ['link' => $link]), $settings['helpers']);
    }

    private function getCollectionItemLink($data, $settings)
    {
        $permalink = $settings['permalink']->__invoke($data);

        return $this->outputPathResolver->link(dirname($permalink), basename($permalink), 'html');
    }
}
