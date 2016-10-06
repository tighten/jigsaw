<?php namespace TightenCo\Jigsaw;

use Exception;
use TightenCo\Jigsaw\IterableObject;
use TightenCo\Jigsaw\IterableObjectWithDefault;

class CollectionDataLoader
{
    private $collectionSettings;
    private $filesystem;
    private $pathResolver;
    private $handlers;
    private $source;
    private $configSettings;

    public function __construct($collectionSettings, $filesystem, $pathResolver, $handlers = [])
    {
        $this->collectionSettings = $collectionSettings;
        $this->filesystem = $filesystem;
        $this->pathResolver = $pathResolver;
        $this->handlers = collect($handlers);
    }

    public function load($source, $configSettings)
    {
        $this->source = $source;
        $this->configSettings = $configSettings;

        return $this->collectionSettings->map(function ($collectionSettings, $collectionName) {
            $collection = Collection::withSettings($collectionSettings, $collectionName);
            $collection->loadItems($this->buildCollection($collection));

            return $collection->map(function($item) {
                return $this->addCollectionItemContent($item);
            });
        })->all();
    }

    private function buildCollection($collection)
    {
        return collect($this->filesystem->allFiles("{$this->source}/_{$collection->name}"))
            ->reject(function ($file) {
                return starts_with($file->getFilename(), '_');
            })->map(function ($file) {
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
        $file = collect($this->filesystem->getFile($item->_source, $item->filename, $item->extension))->first();

        if ($file) {
            $item->setContent($this->getHandler($file)->getItemContent($file));
        }

        return $item;
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
        $data['_source'] = $file->getPath();
        $data['collection'] = $collection->name;
        $data['filename'] = $file->getFilenameWithoutExtension();
        $data['extension'] = $file->getFullExtension();
        $data['path'] = $this->getPermalink($collection, $data);
        $data['url'] = $this->buildUrls($data['path']);

        return $data;
    }

    private function buildUrls($paths)
    {
        $urls = collect($paths)->map(function($path) {
            return rtrim(array_get($this->configSettings, 'baseUrl'), '/') . $path;
        });

        return $urls->count() ? new IterableObjectWithDefault($urls) : null;
    }

    private function getPermalink($collection, $data)
    {
        $links = $this->pathResolver->link($collection->getPermalink(), new IterableObject($data));

        return $links->count() ? new IterableObjectWithDefault($links) : null;
    }
}
