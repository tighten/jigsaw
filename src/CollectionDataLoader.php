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
        return $this->settings->map(function ($settings, $collectionName) use ($source) {
            $settings = array_merge([
                'helpers' => [],
                'permalink' => function($data) {
                    return $data['filename'];
                },
            ], $settings);
            $data = $this->loadSingleCollectionData($source, $collectionName, $settings);

            return $this->sortSingleCollectionData($data, $settings);
        })->all();
    }

    private function loadSingleCollectionData($source, $collectionName, $settings)
    {
        return collect($this->filesystem->allFiles("{$source}/_{$collectionName}"))
            ->map(function ($file) use ($settings) {
                return $this->buildCollectionItem($file, $settings);
            });
    }

    private function sortSingleCollectionData($data, $settings)
    {
        return collect(array_get($settings, 'sort'))
            ->reverse()
            ->reduce(function ($sortedData, $sortSetting) {
                return $this->sortCollectionUsingSetting($sortedData, $sortSetting);
            }, $data);
    }

    private function sortCollectionUsingSetting($data, $sortSetting)
    {
        $sortKey = trim($sortSetting, '-+');
        $sortFunction = $sortSetting[0] === '-' ? 'sortByDesc' : 'sortBy';

        return $data->{$sortFunction}(function ($item, $_) use ($sortKey) {
            return $item->{$sortKey};
        });
    }

    private function buildCollectionItem($file, $settings)
    {
        $handler = $this->handlers->first(function ($_, $handler) use ($file) {
            return $handler->shouldHandle($file);
        }, function () { throw new Exception('No matching collection item handler'); });

        $data = array_merge(
            ['filename' => $this->getFilenameWithoutExtension($file)],
            array_get($settings, 'variables', []),
            $handler->getData($file)
        );
        $link = $this->getCollectionItemLink($data, $settings);

        return new CollectionItem(array_merge($data, ['link' => $link]), $settings['helpers']);
    }

    private function getFilenameWithoutExtension($file)
    {
        return $file->getBasename('.' . $file->getExtension());
    }

    private function getCollectionItemLink($data, $settings)
    {
        $permalink = slugify($settings['permalink']->__invoke($data));

        return $this->outputPathResolver->link(dirname($permalink), basename($permalink), 'html');
    }
}
