<?php namespace TightenCo\Jigsaw;

use Exception;

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
        return $this->settings->map(function ($settings, $collectionName) use ($source) {
            $collection = Collection::withSettings($settings, $collectionName);

            return $collection->loadItems($this->buildCollection($source, $collection));
        })->all();
    }

    private function buildCollection($source, $collection)
    {
        return collect($this->filesystem->allFiles("{$source}/_{$collection->name}"))
            ->map(function ($file) use ($source, $collection) {
                return $this->buildCollectionItem(new InputFile($file, $source), $collection);
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

        $data = array_merge($collection->getDefaultVariables(), $handler->getData($file), $this->getMeta($file));

        return CollectionItem::build(
            $collection,
            array_merge($data, ['link' => $this->getPermalink($collection, $data)]),
            $collection->getHelpers()
        );
    }

    private function getMeta($file)
    {
        $meta['filename'] = $file->getFilenameWithoutExtension();

        return $meta;
    }

    private function getPermalink($collection, $data)
    {
        $permalink = $collection->getPermalink()->__invoke($data);

        return rtrim($this->outputPathResolver->link(dirname($permalink), basename($permalink), 'html'), '/');
    }
}
