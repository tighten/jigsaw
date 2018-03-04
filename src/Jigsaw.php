<?php namespace TightenCo\Jigsaw;

class Jigsaw
{
    public $app;
    protected $env;
    protected $outputLinks;
    protected $siteData;
    protected $dataLoader;
    protected $siteBuilder;

    public function __construct($app, $dataLoader, $siteBuilder)
    {
        $this->app = $app;
        $this->dataLoader = $dataLoader;
        $this->siteBuilder = $siteBuilder;
    }

    public function build($env)
    {
        $this->env = $env;

        $this->app->events->fire('start', $this);

        $this->siteData = $this->dataLoader->loadSiteData($this->app->config);
        $collectionData = $this->dataLoader->loadCollectionData($this->siteData, $this->getDestinationPath());

        $this->app->events->fire('beforeBuild', $this);

        $this->outputLinks = $this->siteBuilder->build(
            $this->getSourcePath(),
            $this->getDestinationPath(),
            $this->siteData->addCollectionData($collectionData)
        );

        $this->app->events->fire('afterBuild', $this);
    }

    public function getEnvironment()
    {
        return $this->env;
    }

    public function getCollection($collection)
    {
        return $this->siteData ? $this->siteData->get($collection) : $this->app->config->collections->get($collection);
    }

    public function getCollections()
    {
        return $this->siteData ? $this->siteData->forget('page') : $this->app->config->collections;
    }

    public function getConfig($key = null)
    {
        return $key ? $this->app->config->get($key) : $this->app->config;
    }

    public function putConfig($key, $value)
    {
        return $this->app->config->put($key, $value);
    }

    public function getDestinationPath()
    {
        return $this->app->buildPath['destination'];
    }

    public function getFilesystem()
    {
        return $this->app->make(Filesystem::class);
    }

    public function getOutputLinks()
    {
        return $this->outputLinks ?: [];
    }

    public function getSourcePath()
    {
        return $this->app->buildPath['source'];
    }

    public function getSourceFile($fileName)
    {
        return $this->getFilesystem()->getFile($this->getSourcePath(), $fileName);
    }

    public function getOutputFile($fileName)
    {
        return $this->getFilesystem()->getFile($this->getDestinationPath(), $fileName);
    }

    public function putSourceFile($fileName, $contents)
    {
        return $this->getFilesystem()->put($this->getSourcePath() . '/' . $fileName, $file);
    }

    public function putOutputFile($fileName, $contents)
    {
        return $this->getFilesystem()->put($this->getDestinationPath() . '/' . $fileName, $file);
    }
}
