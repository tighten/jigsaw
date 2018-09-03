<?php

namespace TightenCo\Jigsaw\Scaffold;

use Exception;
use TightenCo\Jigsaw\File\Filesystem;

class PresetPackage
{
    const PRESETS = [
        'blog' => 'tightenco/jigsaw-preset-blog',
        'docs' => 'tightenco/jigsaw-preset-docs',
    ];

    public $name;
    public $nameShort;
    public $path;
    public $preset;
    public $suffix;
    public $vendor;
    protected $builder;
    protected $customInstaller;
    protected $defaultInstaller;
    protected $files;

    public function __construct(DefaultInstaller $default, CustomInstaller $custom)
    {
        $this->defaultInstaller = $default;
        $this->customInstaller = $custom;
    }

    public function init($preset, ScaffoldBuilder $builder)
    {
        $this->preset = $preset;
        $this->builder = $builder;
        $this->files = new Filesystem();
        $this->resolveNames();
        $this->resolvePath();
    }

    public function runInstaller()
    {
        if (! $this->files->exists($this->path . '/init.php')) {
            $this->runDefaultInstaller();
        }

        try {
            $init = $this->customInstaller->install($this->builder);
            $initFile = include($this->path . '/init.php');

            if (is_array($initFile) && count($initFile)) {
                $this->runDefaultInstaller($initFile);
            }
        } catch (Exception $e) {
            throw new Exception("The 'init.php' file for this preset contains errors.");
        }
    }

    protected function runDefaultInstaller($settings = [])
    {
        $this->defaultInstaller->install($this->builder, $settings);
    }

    protected function resolveNames()
    {
        $name = array_get(self::PRESETS, $this->preset, $this->preset);

        if (! str_contains($name, '/')) {
            throw new Exception("'{$name}' is not a valid package name.");
        }

        $parts = explode('/', $name, 3);
        $this->vendor = array_get($parts, 0);
        $this->name = array_get($parts, 1);
        $this->suffix = array_get($parts, 2);
        $this->shortName = $this->getShortName();
    }

    protected function getShortName()
    {
        return str_contains($this->preset, '/') ?
            explode('/', $this->preset)[1] :
            $this->preset;
    }

    protected function resolvePath()
    {
        $this->path = trim(
            collect([$this->builder->base, 'vendor', $this->vendor, $this->name, $this->suffix])
                ->filter()
                ->implode('/'),
            '/'
        );

        if (! $this->files->exists($this->path)) {
            throw new Exception("The package '{$this->name}' could not be found. \nRun 'composer require {$this->name}' first.");
        }
    }
}
