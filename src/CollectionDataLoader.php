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

            $collection->loadItems($this->buildCollection($collection));

            return $collection->map(function($item) {
                return $this->addCollectionItemContent($item);
            });
        })->all();
    }

    private function buildCollection($collection)
    {
        return collect($this->filesystem->allFiles("{$this->source}/_{$collection->name}"))
            ->map(function ($file) {
                return new InputFile($file, $this->source);
            })->map(function ($inputFile) use ($collection) {
                return $this->buildCollectionItem($inputFile, $collection);
            });
    }

    private function buildCollectionItem($file, $collection)
    {
        $data = array_merge($collection->getDefaultVariables(), $this->getHandler($file)->getItemVariables($file));

        return CollectionItem::build($collection, $this->addMeta($data, $collection, $file), $collection->getHelpers());
    }

    private function addCollectionItemContent($item)
    {
        $file = collect($this->filesystem->getFile($item->source, $item->filename, $item->extension))->first();

        return $file ? $item->put('content', $this->getHandler($file)->getItemContent($file)) : $item;
    }

    private function getHandler($file)
    {
        $handler = $this->handlers->first(function ($_, $handler) use ($file) {
            return $handler->shouldHandle($file);
        });

        if (! $handler) {
            throw new Exception('No matching collection item handler');
        }

        return $handler;
    }

    private function addMeta($data, $collection, $file)
    {
        $data['collection'] = $collection->name;
        $data['filename'] = $file->getFilenameWithoutExtension();
        $data['extension'] = $file->getFullExtension();
        // use _source, and then remove?
        $data['source'] = $file->getPath();
        // use _link, and then remove? Same with _nextItem and _previousItem?
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
