<?php

namespace TightenCo\Jigsaw\File;

class ConfigFile
{
    public $config;

    public function __construct(string $config_path, string $helpers_path = '')
    {
        $config = file_exists($config_path) ? include $config_path : [];
        $helpers = file_exists($helpers_path) ? include $helpers_path : [];

        $this->config = $this->convertStringCollectionsToArray(
            collect($config)->merge($helpers)
        );
    }

    protected function convertStringCollectionsToArray($config)
    {
        $collections = $config->get('collections');

        if ($collections) {
            $config->put('collections', collect($collections)->flatMap(function ($value, $key) {
                return is_array($value) ? [$key => $value] : [$value => []];
            }));
        }

        return $config;
    }

    public static function mergeConfigs($baseConfig, $configToMerge)
    {
        return (new static(''))->convertStringCollectionsToArray(
            array_filter(
                array_replace_recursive(
                    collect($baseConfig)->toArray(),
                    collect($configToMerge)->toArray()
                )
            )
        );
    }
}
