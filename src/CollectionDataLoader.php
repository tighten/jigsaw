<?php namespace TightenCo\Jigsaw;

use Exception;

class CollectionDataLoader
{
    private $settings;
    private $filesystem;
    private $outputPathResolver;
    private $handlers;
    private $source;
    private $globalSettings;

    public function __construct($settings, $filesystem, $outputPathResolver, $handlers = [])
    {
        $this->settings = collect($settings);
        $this->filesystem = $filesystem;
        $this->outputPathResolver = $outputPathResolver;
        $this->handlers = collect($handlers);
    }

    public function load($source, $globalSettings)
    {
        $this->source = $source;
        $this->globalSettings = $globalSettings;

        return $this->settings->map(function ($settings, $collectionName) {
            $collection = Collection::withSettings($settings, $collectionName);

            return $collection->loadItems($this->buildCollection($collection));
        })->all();
    }

    private function buildCollection($collection)
    {
        return collect($this->filesystem->allFiles("{$this->source}/_{$collection->name}"))
            ->map(function ($file) use ($collection) {
                return $this->buildCollectionItem(new InputFile($file, $this->source), $collection);
            });
    }

    private function buildCollectionItem($file, $collection)
    {
        $handler = $this->handlers->first(function ($_, $handler) use ($file) {
            return $handler->shouldHandle($file);
        });

        if (! $handler) {
            throw new Exception('No matching collection item handler');
        }

        $data = array_merge($collection->getDefaultVariables(), $handler->getData($file));

        return CollectionItem::build($collection, $this->addMeta($data, $collection, $file), $collection->getHelpers());
    }

    private function addMeta($data, $collection, $file)
    {
        $data['collection'] = $collection->name;
        $data['filename'] = $file->getFilenameWithoutExtension();
        $data['extension'] = $file->getFullExtension();
        $data['link'] = $this->getPermalink($collection, $data);
        $data['path'] = trim($data['link'], '/');
        $data['url'] = rtrim(array_get($this->globalSettings, 'baseUrl'), '/') . '/' . trim($data['link'], '/');

        return $data;
    }

    private function getPermalink($collection, $data)
    {
        if (! array_get($data, 'extends')) {
            return;
        }

        $permalink = $collection->getPermalink()->__invoke($data);

        return rtrim($this->outputPathResolver->link(dirname($permalink), basename($permalink), 'html'), '/');
    }
}
