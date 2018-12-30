<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw;

use Illuminate\Support\Collection;
use TightenCo\Jigsaw\File\Filesystem;

class Jigsaw
{
    public $app;
    protected $env;
    protected $outputPaths;
    protected $siteData;
    protected $dataLoader;
    protected $siteBuilder;
    protected $verbose;

    public function __construct($app, $dataLoader, $remoteItemLoader, $siteBuilder)
    {
        $this->app = $app;
        $this->dataLoader = $dataLoader;
        $this->remoteItemLoader = $remoteItemLoader;
        $this->siteBuilder = $siteBuilder;
    }

    public function build($env, $useCache = false): Jigsaw
    {
        $this->env = $env;
        $this->siteData = $this->dataLoader->loadSiteData($this->app->config);

        return $this->fireEvent('beforeBuild')
            ->buildCollections()
            ->fireEvent('afterCollections')
            ->buildSite($useCache)
            ->fireEvent('afterBuild')
            ->cleanup();
    }

    protected function buildCollections(): Jigsaw
    {
        $this->remoteItemLoader->write($this->siteData->collections, $this->getSourcePath());
        $collectionData = $this->dataLoader->loadCollectionData($this->siteData, $this->getSourcePath());
        $this->siteData = $this->siteData->addCollectionData($collectionData);

        return $this;
    }

    protected function buildSite($useCache): Jigsaw
    {
        $this->outputPaths = $this->siteBuilder
            ->setUseCache($useCache)
            ->build(
                $this->getSourcePath(),
                $this->getDestinationPath(),
                $this->siteData
            );

        return $this;
    }

    protected function cleanup(): Jigsaw
    {
        $this->remoteItemLoader->cleanup();

        return $this;
    }

    protected function fireEvent($event): Jigsaw
    {
        $this->app->events->fire($event, $this);

        return $this;
    }

    public function getSiteData(): SiteData
    {
        return $this->siteData;
    }

    public function getEnvironment(): string
    {
        return $this->env;
    }

    /**
     * @return mixed
     */
    public function getCollection($collection)
    {
        return $this->siteData->get($collection);
    }

    public function getCollections(): Collection
    {
        return $this->siteData->get('collections') ?
            $this->siteData->get('collections')->keys() :
            $this->siteData->except('page');
    }

    /**
     * @return mixed
     */
    public function getConfig($key = null)
    {
        return $key ? data_get($this->siteData->page, $key) : $this->siteData->page;
    }

    public function setConfig($key, $value): Jigsaw
    {
        $this->siteData->set($key, $value);
        $this->siteData->page->set($key, $value);

        return $this;
    }

    public function getSourcePath(): string
    {
        return $this->app->buildPath['source'];
    }

    public function setSourcePath($path): Jigsaw
    {
        $this->app->buildPath = [
            'source' => $path,
            'destination' => $this->app->buildPath['destination'],
        ];

        return $this;
    }

    public function getDestinationPath(): string
    {
        return $this->app->buildPath['destination'];
    }

    public function setDestinationPath($path): Jigsaw
    {
        $this->app->buildPath = [
            'source' => $this->app->buildPath['source'],
            'destination' => $path,
        ];

        return $this;
    }

    public function getFilesystem(): Filesystem
    {
        return $this->app->make(Filesystem::class);
    }

    /**
     * @return string[]
     */
    public function getOutputPaths(): array
    {
        return $this->outputPaths ?: [];
    }

    public function readSourceFile($fileName): string
    {
        return $this->getFilesystem()->get($this->getSourcePath() . '/' . $fileName);
    }

    public function writeSourceFile($fileName, $contents): void
    {
        $this->getFilesystem()->putWithDirectories($this->getSourcePath() . '/' . $fileName, $contents);
    }

    public function readOutputFile($fileName): string
    {
        return $this->getFilesystem()->get($this->getDestinationPath() . '/' . $fileName);
    }

    public function writeOutputFile($fileName, $contents): void
    {
        $this->getFilesystem()->putWithDirectories($this->getDestinationPath() . '/' . $fileName, $contents);
    }
}
