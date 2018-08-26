<?php

namespace TightenCo\Jigsaw;

use Symfony\Component\Console\Output\ConsoleOutput;
use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\File\TemporaryFilesystem;

class Jigsaw
{
    public $app;
    protected $env;
    protected $outputPaths;
    protected $siteData;
    protected $dataLoader;
    protected $siteBuilder;
    protected $consoleOutput;
    protected $verbose;

    public function __construct($app, $dataLoader, $remoteItemLoader, $siteBuilder)
    {
        $this->app = $app;
        $this->dataLoader = $dataLoader;
        $this->remoteItemLoader = $remoteItemLoader;
        $this->siteBuilder = $siteBuilder;
        $this->consoleOutput = new ConsoleOutput();
    }

    public function setVerbose($verbose)
    {
        $this->consoleOutput->setVerbosity($verbose ? 0 : -1);

        return $this;
    }

    public function build($env)
    {
        $this->env = $env;
        $this->siteData = $this->dataLoader->loadSiteData($this->app->config);

        return $this->fireEvent('beforeBuild')
            ->buildCollections()
            ->fireEvent('afterCollections')
            ->buildSite()
            ->fireEvent('afterBuild')
            ->cleanup();
    }

    protected function buildCollections()
    {
        $this->consoleOutput->writeln('<comment>Loading collections ...</comment>');
        $this->remoteItemLoader->write($this->siteData->collections, $this->getSourcePath());
        $collectionData = $this->dataLoader->loadCollectionData($this->siteData, $this->getSourcePath());
        $this->siteData = $this->siteData->addCollectionData($collectionData);

        return $this;
    }

    protected function buildSite()
    {
        $this->outputPaths = $this->siteBuilder
            ->setConsoleOutput($this->consoleOutput)
            ->build(
                $this->getSourcePath(),
                $this->getDestinationPath(),
                $this->siteData
            );

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
