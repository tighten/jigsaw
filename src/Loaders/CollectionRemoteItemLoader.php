<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Loaders;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection as BaseCollection;
use JsonSerializable;
use TightenCo\Jigsaw\Collection\Collection;
use TightenCo\Jigsaw\Collection\CollectionRemoteItem;
use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\IterableObject;
use Traversable;

class CollectionRemoteItemLoader
{
    /** @var Filesystem */
    private $files;

    /** @var string[] */
    private $tempDirectories = [];

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * @param array|BaseCollection|Arrayable|Jsonable|JsonSerializable|Traversable $collections
     */
    public function write($collections, string $source): void
    {
        collect($collections)->each(function ($collection, string $collectionName) use ($source): void {
            $items = $this->getItems($collection);

            if ($items->count()) {
                $this->writeTempFiles($items, $this->createTempDirectory($source, $collectionName), $collectionName);
            }
        });
    }

    private function createTempDirectory(string $source, string $collectionName): string
    {
        $tempDirectory = $source . '/_' . $collectionName . '/_tmp';
        $this->prepareDirectory($tempDirectory, true);
        $this->tempDirectories[] = $tempDirectory;

        return $tempDirectory;
    }

    public function cleanup(): void
    {
        collect($this->tempDirectories)->each(function (string $path): void {
            $this->files->deleteDirectory($path);
        });
    }

    private function getItems(IterableObject $collection): BaseCollection
    {
        if (! $collection->items) {
            return collect();
        }

        return collect(
            is_callable($collection->items) ?
            $collection->items->__invoke() :
            $collection->items
        );
    }

    private function prepareDirectory(string $directory, bool $clean = false): void
    {
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        if ($clean) {
            $this->files->cleanDirectory($directory);
        }
    }

    /**
     * @param array|Collection|Arrayable|Jsonable|JsonSerializable|Traversable $items
     */
    private function writeTempFiles($items, string $directory, string $collectionName): void
    {
        collect($items)->each(function ($item, int $index) use ($directory, $collectionName): void {
            $this->writeFile(new CollectionRemoteItem($item, $index, $collectionName), $directory);
        });
    }

    private function writeFile(CollectionRemoteItem $remoteFile, string $directory): void
    {
        $this->files->put($directory . '/' . $remoteFile->getFilename(), $remoteFile->getContent());
    }
}
