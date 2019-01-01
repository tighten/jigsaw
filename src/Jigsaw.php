<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\Loaders\CollectionRemoteItemLoader;
use TightenCo\Jigsaw\Loaders\DataLoader;

class Jigsaw
{
    /** @var Container */
    public $app;

    /** @var string */
    protected $env;

    /** @var string[]|Collection */
    protected $outputPaths;

    /** @var SiteData */
    protected $siteData;

    /** @var DataLoader */
    protected $dataLoader;

    /** @var SiteBuilder */
    protected $siteBuilder;

    /** @deprecated unused */
    protected $verbose;

    /** @var CollectionRemoteItemLoader */
    protected $remoteItemLoader;

    public function __construct(Container $app, DataLoader $dataLoader, CollectionRemoteItemLoader $remoteItemLoader, SiteBuilder $siteBuilder)
    {
        $this->app = $app;
        $this->dataLoader = $dataLoader;
        $this->remoteItemLoader = $remoteItemLoader;
        $this->siteBuilder = $siteBuilder;
    }

    public function build(string $env, bool $useCache = false): Jigsaw
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

    protected function buildSite(bool $useCache): Jigsaw
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

    protected function fireEvent(string $event): Jigsaw
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
     * @param string|int $collection
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
     * @param ?string|?int $key
     * @return mixed
     */
    public function getConfig($key = null)
    {
        return $key ? data_get($this->siteData->page, $key) : $this->siteData->page;
    }

    /**
     * @param string|int $key
     * @param ?mixed     $value
     */
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

    public function setSourcePath(string $path): Jigsaw
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

    public function setDestinationPath(string $path): Jigsaw
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

    public function readSourceFile(string $fileName): string
    {
        return $this->getFilesystem()->get($this->getSourcePath() . '/' . $fileName);
    }

    public function writeSourceFile(string $fileName, string $contents): void
    {
        $this->getFilesystem()->putWithDirectories($this->getSourcePath() . '/' . $fileName, $contents);
    }

    public function readOutputFile(string $fileName): string
    {
        return $this->getFilesystem()->get($this->getDestinationPath() . '/' . $fileName);
    }

    public function writeOutputFile(string $fileName, string $contents): void
    {
        $this->getFilesystem()->putWithDirectories($this->getDestinationPath() . '/' . $fileName, $contents);
    }
}
