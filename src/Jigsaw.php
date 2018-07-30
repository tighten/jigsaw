<?php namespace TightenCo\Jigsaw;

use TightenCo\Jigsaw\File\Filesystem;

class Jigsaw
{
    public $app;
    protected $env;
    protected $outputPaths;
    protected $siteData;
    protected $dataLoader;
    protected $siteBuilder;

    public function __construct($app, $dataLoader, $remoteItemLoader, $siteBuilder)
    {
        $this->app = $app;
        $this->dataLoader = $dataLoader;
        $this->remoteItemLoader = $remoteItemLoader;
        $this->siteBuilder = $siteBuilder;
    }

    public function build($env)
    {
        $this->env = $env;
        $this->siteData = $this->dataLoader->loadSiteData($this->app->config);
        $this->fireEvent('beforeBuild');

        $this->remoteItemLoader->write($this->siteData->collections, $this->getSourcePath());
        $collectionData = $this->dataLoader->loadCollectionData($this->siteData, $this->getSourcePath());
        $this->siteData = $this->siteData->addCollectionData($collectionData);

        $this->fireEvent('afterCollections');

        $this->outputPaths = $this->siteBuilder->build(
            $this->getSourcePath(),
            $this->getDestinationPath(),
            $this->siteData
        );

        $this->remoteItemLoader->cleanup();

        $this->fireEvent('afterBuild');
    }

    protected function fireEvent($event)
    {
        $this->app->events->fire($event, $this);
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
    }

    public function getFilesystem()
    {
        return $this->app->make(Filesystem::class);
    }

    public function getOutputPaths()
    {
        return $this->outputPaths ?: [];
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
