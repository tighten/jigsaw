<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Loaders;

use TightenCo\Jigsaw\Collection\CollectionRemoteItem;
use TightenCo\Jigsaw\File\Filesystem;

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

    public function write($collections, $source): void
    {
        collect($collections)->each(function ($collection, $collectionName) use ($source): void {
            $items = $this->getItems($collection);

            if (collect($items)->count()) {
                $this->writeTempFiles($items, $this->createTempDirectory($source, $collectionName), $collectionName);
            }
        });
    }

    private function createTempDirectory($source, $collectionName): string
    {
        $tempDirectory = $source . '/_' . $collectionName . '/_tmp';
        $this->prepareDirectory($tempDirectory, true);
        $this->tempDirectories[] = $tempDirectory;

        return $tempDirectory;
    }

    public function cleanup(): void
    {
        collect($this->tempDirectories)->each(function ($path): void {
            $this->files->deleteDirectory($path);
        });
    }

    private function getItems($collection): array
    {
        if (! $collection->items) {
            return [];
        }

        return is_callable($collection->items) ?
            $collection->items->__invoke() :
            $collection->items->toArray();
    }

    private function prepareDirectory($directory, $clean = false): void
    {
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        if ($clean) {
            $this->files->cleanDirectory($directory);
        }
    }

    private function writeTempFiles($items, $directory, $collectionName): void
    {
        collect($items)->each(function ($item, $index) use ($directory, $collectionName): void {
            $this->writeFile(new CollectionRemoteItem($item, $index, $collectionName), $directory);
        });
    }

    private function writeFile($remoteFile, $directory): void
    {
        $this->files->put($directory . '/' . $remoteFile->getFilename(), $remoteFile->getContent());
    }
}
