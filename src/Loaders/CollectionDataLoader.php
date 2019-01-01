<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Loaders;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection as BaseCollection;
use JsonSerializable;
use Symfony\Component\Finder\SplFileInfo;
use TightenCo\Jigsaw\Collection\Collection;
use TightenCo\Jigsaw\Collection\CollectionItem;
use TightenCo\Jigsaw\CollectionItemHandlers\BladeCollectionItemHandler;
use TightenCo\Jigsaw\Console\ConsoleOutput;
use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\File\InputFile;
use TightenCo\Jigsaw\Handlers\DefaultHandler;
use TightenCo\Jigsaw\IterableObject;
use TightenCo\Jigsaw\IterableObjectWithDefault;
use TightenCo\Jigsaw\PageVariable;
use TightenCo\Jigsaw\PathResolvers\CollectionPathResolver;
use TightenCo\Jigsaw\SiteData;
use Traversable;

class CollectionDataLoader
{
    /** @var Filesystem */
    private $filesystem;

    /** @var ConsoleOutput */
    private $consoleOutput;

    /** @var CollectionPathResolver */
    private $pathResolver;

    /** @var BaseCollection */
    private $handlers;

    /** @var string */
    private $source;

    /** @var IterableObject */
    private $pageSettings;

    /** @var Collection */
    private $collectionSettings;

    /**
     * @param BladeCollectionItemHandler[] $handlers TODO use interface instead of class
     */
    public function __construct(Filesystem $filesystem, ConsoleOutput $consoleOutput, CollectionPathResolver $pathResolver, array $handlers = [])
    {
        $this->filesystem = $filesystem;
        $this->pathResolver = $pathResolver;
        $this->handlers = collect($handlers);
        $this->consoleOutput = $consoleOutput;
    }

    public function load(SiteData $siteData, string $source): array
    {
        $this->source = $source;
        $this->pageSettings = $siteData->page;
        $this->collectionSettings = collect($siteData->collections);
        $this->consoleOutput->startProgressBar('collections');

        $collections = $this->collectionSettings->map(function ($collectionSettings, $collectionName): BaseCollection {
            $collection = Collection::withSettings($collectionSettings, $collectionName);
            $collection->loadItems($this->buildCollection($collection));

            return $collection->updateItems($collection->map(function ($item): CollectionItem {
                return $this->addCollectionItemContent($item);
            }));
        });

        return $collections->all();
    }

    private function buildCollection(Collection $collection): BaseCollection
    {
        $path = "{$this->source}/_{$collection->name}";

        if (! $this->filesystem->exists($path)) {
            return collect();
        }

        return collect($this->filesystem->files($path))
            ->reject(function (SplFileInfo $file): bool {
                return starts_with($file->getFilename(), '_');
            })->tap(function (BaseCollection $files): void {
                $this->consoleOutput->progressBar('collections')->addSteps($files->count());
            })->map(function (SplFileInfo $file): InputFile {
                return new InputFile($file);
            })->map(function (InputFile $inputFile) use ($collection): CollectionItem {
                $this->consoleOutput->progressBar('collections')->advance();

                return $this->buildCollectionItem($inputFile, $collection);
            });
    }

    private function buildCollectionItem(InputFile $file, Collection $collection): CollectionItem
    {
        $data = $this->pageSettings
            ->merge(['section' => 'content'])
            ->merge($collection->settings)
            ->merge($this->getHandler($file)->getItemVariables($file));
        $data->put('_meta', new IterableObject($this->getMetaData($file, $collection, $data)));
        $path = $this->getPath($data);
        $data->_meta->put('path', $path)->put('url', $this->buildUrls($path));

        return CollectionItem::build($collection, $data);
    }

    private function addCollectionItemContent(CollectionItem $item): CollectionItem
    {
        $file = $this->filesystem->getFile($item->getSource(), $item->getFilename() . '.' . $item->getExtension());

        if ($file) {
            $item->setContent($this->getHandler($file)->getItemContent($file));
        }

        return $item;
    }

    private function getHandler(InputFile $file): ?DefaultHandler // TODO use interface of class
    {
        $handler = $this->handlers->first(function (DefaultHandler $handler/* TODO use interface instead of class */) use ($file): bool {
            return $handler->shouldHandle($file);
        });

        if (! $handler) {
            throw new Exception('No matching collection item handler');
        }

        return $handler;
    }

    private function getMetaData(InputFile $file, Collection $collection, $data): array
    {
        $filename = $file->getFilenameWithoutExtension();
        $baseUrl = $data->baseUrl;
        $extension = $file->getFullExtension();
        $collectionName = $collection->name;
        $collection = $collectionName;
        $source = $file->getPath();

        return compact('filename', 'baseUrl', 'extension', 'collection', 'collectionName', 'source');
    }


    /**
     * @param array|Collection|Arrayable|Jsonable|JsonSerializable|Traversable $paths
     */
    private function buildUrls($paths): ?IterableObjectWithDefault
    {
        $urls = collect($paths)->map(function ($path): string {
            return rightTrimPath($this->pageSettings->get('baseUrl')) . '/' . trimPath($path);
        });

        return $urls->count() ? new IterableObjectWithDefault($urls) : null;
    }

    private function getPath(IterableObject $data): ?IterableObjectWithDefault
    {
        $links = $this->pathResolver->link((string) $data->path, new PageVariable($data));

        return $links->count() ? new IterableObjectWithDefault($links) : null;
    }
}
