<?php

namespace TightenCo\Jigsaw\Loaders;

use Illuminate\Support\Collection;
use TightenCo\Jigsaw\Collection\CollectionRemoteItem;
use TightenCo\Jigsaw\File\Filesystem;

class CollectionRemoteItemLoader
{
    private $config;
    private $files;
    private $tempDirectories;

    public function __construct(Collection $config, Filesystem $files)
    {
        $this->config = $config;
        $this->files = $files;
    }

    public function write($collections, $source)
    {
        collect($collections)->each(function ($collection, $collectionName) use ($source) {
            $items = $this->getItems($collection);

            if (collect($items)->count()) {
                $this->writeTempFiles($items, $this->createTempDirectory($source, $collectionName), $collectionName);
            }
        });
    }

    private function createTempDirectory($source, $collectionName)
    {
        $tempDirectory = $source . '/_' . $collectionName . '/_tmp';
        $this->prepareDirectory($tempDirectory, true);
        $this->tempDirectories[] = $tempDirectory;

        return $tempDirectory;
    }

    public function cleanup()
    {
        collect($this->tempDirectories)->each(function ($path) {
            $this->files->deleteDirectory($path);

            if ($this->files->isEmptyDirectory($parent = $this->files->dirname($path))) {
                $this->files->deleteDirectory($parent);
            }
        });
    }

    private function getItems($collection)
    {
        if (! $collection->items) {
            return;
        }

        return is_callable($collection->items) ?
            $collection->items->__invoke($this->config) :
            $collection->items->toArray();
    }

    private function prepareDirectory($directory, $clean = false)
    {
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        if ($clean) {
            $this->files->cleanDirectory($directory);
        }
    }

    private function writeTempFiles($items, $directory, $collectionName)
    {
        collect($items)->each(function ($item, $index) use ($directory, $collectionName) {
            $this->writeFile(new CollectionRemoteItem($item, $index, $collectionName), $directory);
        });
    }

    private function writeFile($remoteFile, $directory)
    {
        $this->files->put($directory . '/' . $remoteFile->getFilename(), $remoteFile->getContent());
    }
}
