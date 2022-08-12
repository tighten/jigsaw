<?php

namespace TightenCo\Jigsaw;

use Illuminate\Support\Traits\Macroable;
use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\Loaders\DataLoader;
use TightenCo\Jigsaw\Loaders\CollectionRemoteItemLoader;
use Illuminate\Contracts\Container\Container;

class Jigsaw
{
    use Macroable;

    public $app;
    protected $env;
    protected $pageInfo;
    protected $outputPaths;
    protected $siteData;
    protected $dataLoader;
    protected $remoteItemLoader;
    protected $siteBuilder;
    protected $verbose;
    protected static $commands = [];

    public function __construct(
        Container $app,
        DataLoader $dataLoader,
        CollectionRemoteItemLoader $remoteItemLoader,
        SiteBuilder $siteBuilder
    ) {
        $this->app = $app;
        $this->dataLoader = $dataLoader;
        $this->remoteItemLoader = $remoteItemLoader;
        $this->siteBuilder = $siteBuilder;
    }

    public function build($env, $useCache = false)
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

    public static function registerCommand($command)
    {
        self::$commands[] = $command;
    }

    public static function addUserCommands($app, $container)
    {
        foreach (self::$commands as $command) {
            $app->add(new $command($container));
        }
    }

    protected function buildCollections()
    {
        $this->remoteItemLoader->write($this->siteData->collections, $this->getSourcePath());
        $collectionData = $this->dataLoader->loadCollectionData($this->siteData, $this->getSourcePath());
        $this->siteData = $this->siteData->addCollectionData($collectionData);

        return $this;
    }

    protected function buildSite($useCache)
    {
        $this->pageInfo = $this->siteBuilder
            ->setUseCache($useCache)
            ->build(
                $this->getSourcePath(),
                $this->getDestinationPath(),
                $this->siteData
            );
        $this->outputPaths = $this->pageInfo->keys();

        return $this;
    }

    protected function cleanup()
    {
        $this->remoteItemLoader->cleanup();

        return $this;
    }

    protected function fireEvent($event)
    {
        $this->app->events->fire($event, $this);

        return $this;
    }

    public function getSiteData()
    {
        return $this->siteData;
    }

    public function getEnvironment()
    {
        return $this->env;
    }

    public function getCollection($collection)
    {
        return $this->siteData->get($collection);
    }

    public function getCollections()
    {
        return $this->siteData->get('collections') ?
            $this->siteData->get('collections')->keys() :
            $this->siteData->except('page');
    }

    public function getConfig($key = null)
    {
        return $key ? data_get($this->siteData->page, $key) : $this->siteData->page;
    }

    public function setConfig($key, $value)
    {
        $this->siteData->set($key, $value);
        $this->siteData->page->set($key, $value);

        return $this;
    }

    public function getSourcePath()
    {
        return $this->app->buildPath['source'];
    }

    public function setSourcePath($path)
    {
        $this->app->buildPath = [
            'source' => $path,
            'destination' => $this->app->buildPath['destination'],
        ];

        return $this;
    }

    public function getDestinationPath()
    {
        return $this->app->buildPath['destination'];
    }

    public function setDestinationPath($path)
    {
        $this->app->buildPath = [
            'source' => $this->app->buildPath['source'],
            'destination' => $path,
        ];

        return $this;
    }

    public function getFilesystem()
    {
        return $this->app->make(Filesystem::class);
    }

    public function getOutputPaths()
    {
        return $this->outputPaths ?: collect();
    }

    public function getPages()
    {
        return $this->pageInfo ?: collect();
    }

    public function readSourceFile($fileName)
    {
        return $this->getFilesystem()->get($this->getSourcePath() . '/' . $fileName);
    }

    public function writeSourceFile($fileName, $contents)
    {
        return $this->getFilesystem()->putWithDirectories($this->getSourcePath() . '/' . $fileName, $contents);
    }

    public function readOutputFile($fileName)
    {
        return $this->getFilesystem()->get($this->getDestinationPath() . '/' . $fileName);
    }

    public function writeOutputFile($fileName, $contents)
    {
        return $this->getFilesystem()->putWithDirectories($this->getDestinationPath() . '/' . $fileName, $contents);
    }
}
