<?php namespace TightenCo\Jigsaw;

use Exception;
use TightenCo\Jigsaw\IterableObject;
use TightenCo\Jigsaw\IterableObjectWithDefault;

class CollectionDataLoader
{
    private $settings;
    private $filesystem;
    private $pathResolver;
    private $handlers;
    private $source;
    private $globalSettings;

    public function __construct($settings, $filesystem, $pathResolver, $handlers = [])
    {
        $this->settings = collect($settings);
        $this->filesystem = $filesystem;
        $this->pathResolver = $pathResolver;
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
        $data['path'] = $this->buildPaths($data['link']);
        $data['url'] = $this->buildUrls($data['path']);

        return $data;
    }

    private function buildPaths($links)
    {
        $paths = collect($links)->map(function($link) {
            return trim($link, '/');
        });

        return $paths->count() ? new IterableObjectWithDefault($paths) : null;
    }

    private function buildUrls($paths)
    {
        $urls = collect($paths)->map(function($path) {
            return rtrim(array_get($this->globalSettings, 'baseUrl'), '/') . '/' . $path;
        });

        return $urls->count() ? new IterableObjectWithDefault($urls) : null;
    }

    private function getPermalink($collection, $data)
    {
        $links = $this->pathResolver->link($collection->getPermalink(), new IterableObject($data));

        return $links->count() ? new IterableObjectWithDefault($links) : null;
    }
}
