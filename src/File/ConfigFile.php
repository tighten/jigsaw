<?php

namespace TightenCo\Jigsaw\File;

class ConfigFile
{
    public $config;

    public function __construct($config_path, $helpers_path = '')
    {
        $config = file_exists($config_path) ? include $config_path : [];
        $helpers = file_exists($helpers_path) ? include $helpers_path : [];

        $this->config = array_merge($config, $helpers);
        $this->convertStringCollectionsToArray();
    }

    protected function convertStringCollectionsToArray()
    {
        $collections = array_get($this->config, 'collections');

        if ($collections) {
            $this->config['collections'] = collect($collections)->flatMap(function ($value, $key) {
                return is_array($value) ? [$key => $value] : [$value => []];
            });
        }
    }
}
