<?php namespace TightenCo\Jigsaw;

class DataLoader
{
    private $config;
    private $basePath;
    private $collectionDataLoader;

    public function __construct($config, $basePath, $collectionDataLoader)
    {
        $this->config = $config;
        $this->basePath = $basePath;
        $this->collectionDataLoader = $collectionDataLoader;
    }

    public function load($source, $env)
    {
        $siteData = SiteData::build($this->loadConfigData($env));
        $siteData->addCollectionData($this->collectionDataLoader->load($source, $siteData));

        return $siteData;
    }

    private function loadConfigData($env)
    {
        if (file_exists($this->basePath . "/config.{$env}.php")) {
            $environmentConfig = include $this->basePath . "/config.{$env}.php";
        } else {
            $environmentConfig = [];
        }

        return collect($this->config)->merge($environmentConfig);
    }
}
