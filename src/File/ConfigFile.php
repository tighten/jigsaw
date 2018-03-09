<?php namespace TightenCo\Jigsaw\File;

class ConfigFile
{
    public $config;

    public function __construct($file_path)
    {
        $this->config = file_exists($file_path) ? include $file_path : [];
        $this->convertStringCollectionsToArray();
    }

    protected function convertStringCollectionsToArray()
    {
        $collections =  array_get($this->config, 'collections');

        if ($collections) {
            $this->config['collections'] = collect($collections)->flatMap(function ($value, $key) {
                return is_array($value) ? [$key => $value] : [$value => []];
            });
        }
    }
}
