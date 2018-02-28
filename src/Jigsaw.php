<?php namespace TightenCo\Jigsaw;

class Jigsaw
{
    private $app;
    private $dataLoader;
    private $siteBuilder;

    public function __construct($app, $dataLoader, $siteBuilder)
    {
        $this->app = $app;
        $this->dataLoader = $dataLoader;
        $this->siteBuilder = $siteBuilder;
    }

    public function build($env)
    {
        $siteData = $this->dataLoader->loadSiteData($this->app->config);
        $collectionData = $this->dataLoader->loadCollectionData($siteData,$this->app->buildPath['source']);

        $this->siteBuilder->build(
            $this->app->buildPath['source'],
            $this->app->buildPath['destination'],
            $siteData,
            $collectionData
        );
    }
}
