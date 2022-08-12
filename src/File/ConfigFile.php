<?php

namespace TightenCo\Jigsaw\File;

class ConfigFile
{
    public $config;

    public function __construct(string $config_path, string $helpers_path = '')
    {
        $config = file_exists($config_path) ? include $config_path : [];
        $helpers = file_exists($helpers_path) ? include $helpers_path : [];

        $this->config = collect($config)->merge($helpers);
        $this->convertStringCollectionsToArray();
    }

    protected function convertStringCollectionsToArray()
    {
        $collections = $this->config->get('collections');

        if ($collections) {
            $this->config->put('collections', collect($collections)->flatMap(function ($value, $key) {
                return is_array($value) ? [$key => $value] : [$value => []];
            }));
        }
    }
}
