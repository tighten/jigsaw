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
        return $this->makeIterableObject(
            array_merge($this->loadConfigData($env), $this->loadCollectionData($source))
        );
    }

    private function loadConfigData($env)
    {
        if (file_exists($this->basePath . "/config.{$env}.php")) {
            $environmentConfig = include $this->basePath . "/config.{$env}.php";
        } else {
            $environmentConfig = [];
        }

        return array_merge($this->config, $environmentConfig);
    }

    private function makeIterableObject($array)
    {
        return collect($array)->map(function ($item, $_) {
            return is_array($item) ? new IterableObject($this->makeIterableObject($item)) : $item;
        });
    }

    private function loadCollectionData($source)
    {
        return $this->collectionDataLoader->load($source);
    }
}
