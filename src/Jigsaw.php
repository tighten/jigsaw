<?php namespace TightenCo\Jigsaw;

class Jigsaw
{
    private $app;
    private $dataLoader;
    private $remoteItemLoader;
    private $siteBuilder;

    public function __construct($app, $dataLoader, $remoteItemLoader, $siteBuilder)
    {
        $this->app = $app;
        $this->dataLoader = $dataLoader;
        $this->remoteItemLoader = $remoteItemLoader;
        $this->siteBuilder = $siteBuilder;
    }

    public function build($env)
    {
        $siteData = $this->dataLoader->loadSiteData($this->app->config);
        $this->remoteItemLoader->write($siteData->collections, $this->app->buildPath['source']);
        $collectionData = $this->dataLoader->loadCollectionData($siteData, $this->app->buildPath['source']);

        $this->siteBuilder->build(
            $this->app->buildPath['source'],
            $this->app->buildPath['destination'],
            $siteData->addCollectionData($collectionData)
        );

        $this->remoteItemLoader->cleanup();
    }
}
