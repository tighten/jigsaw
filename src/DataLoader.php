<?php namespace TightenCo\Jigsaw;

class DataLoader
{
    private $basePath;

    // Needs ContentCollectionLoader which needs Collection Handlers
    public function __construct($basePath /* $configLoader, $collectionLoader */)
    {
        $this->basePath = $basePath;
    }

    public function load($source, $env)
    {
        return $this->loadConfig($env);
        // return array_merge(
        //     $this->configLoader->load($env),
        //     $this->collectionLoader->load($source)
        // );
    }

    private function loadConfig($env)
    {
        if (file_exists($this->basePath . "/config.{$env}.php")) {
            $environmentConfig = include $this->basePath . "/config.{$env}.php";
        } else {
            $environmentConfig = [];
        }

        return array_merge(include $this->basePath . '/config.php', $environmentConfig);
    }
}
