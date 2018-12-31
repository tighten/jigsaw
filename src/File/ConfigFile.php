<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\File;

class ConfigFile
{
    /** @var array */
    public $config;

    public function __construct(string $file_path)
    {
        $this->config = file_exists($file_path) ? include $file_path : [];
        $this->convertStringCollectionsToArray();
    }

    protected function convertStringCollectionsToArray(): void
    {
        $collections = array_get($this->config, 'collections');

        if ($collections) {
            $this->config['collections'] = collect($collections)->flatMap(function ($value, $key) {
                return is_array($value) ? [$key => $value] : [$value => []];
            });
        }
    }
}
