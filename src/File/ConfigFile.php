<?php

namespace TightenCo\Jigsaw\File;

use Illuminate\Support\Arr;

class ConfigFile
{
    public $config;

    public function __construct($config_path, $helpers_path = '')
    {
        $config = file_exists($config_path) ? include $config_path : [];
        $helpers = file_exists($helpers_path) ? include $helpers_path : [];

        $this->config = $this->convertStringCollectionsToArray(
            array_merge($config, $helpers)
        );
    }

    protected function convertStringCollectionsToArray($config)
    {
        $collections = Arr::get($config, 'collections');

        if ($collections) {
            $config['collections'] = collect($collections)->flatMap(function ($value, $key) {
                return is_array($value) ? [$key => $value] : [$value => []];
            });
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
