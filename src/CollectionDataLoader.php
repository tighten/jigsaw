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

    public function load($source, $siteOptions)
    {
        return $this->settings->map(function ($collectionSettings, $name) use ($source, $siteOptions) {
            return $this->loadSingleCollectionData($source, $name, $collectionSettings, $siteOptions);
        })->all();
    }

    private function loadSingleCollectionData($source, $collectionName, $settings, $siteOptions)
    {
        return collect($this->filesystem->allFiles("{$source}/_{$collectionName}"))->map(function ($file) use ($settings, $siteOptions) {
            return $this->buildCollectionItem($file, $settings, $siteOptions);
        })->all();
    }

    private function buildCollectionItem($file, $settings, $siteOptions)
    {
        $handler = $this->handlers->first(function ($_, $handler) use ($file) {
            return $handler->shouldHandle($file);
        }, function () { throw new Exception('No matching collection item handler'); });

        $data = $handler->getData($file);
        $link = $this->getCollectionItemLink($data, $settings, $siteOptions);

        return new CollectionItem(array_merge($data, ['link' => $link]), $settings['helpers']);
    }

    private function getCollectionItemLink($data, $settings, $siteOptions)
    {
        $link = $settings['permalink']->__invoke($data);

        if ($siteOptions['pretty']) {
            $link = rtrim($link, '/') . '/';
        } else {
            $link .= '.html';
        }

        return '/' . ltrim($link, '/');
    }
}
