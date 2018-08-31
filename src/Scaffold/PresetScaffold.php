<?php

namespace TightenCo\Jigsaw\Scaffold;

use Exception;
use TightenCo\Jigsaw\File\Filesystem;

class PresetScaffold extends Scaffold
{
    const PRESETS = [
        'blog' => 'tightenco/jigsaw-preset-blog',
        'docs' => 'tightenco/jigsaw-preset-documentation',
    ];

    public $packageName;
    public $packagePath;

    public function build($preset)
    {
        $this->packageName = $this->resolvePackageName($preset);
        $this->packagePath = $this->resolvePackagePath();
    }

    public function resolvePackageName($preset)
    {
        $name = array_get(self::PRESETS, $preset, $preset);

        if (! str_contains($name, '/')) {
            throw new Exception("'{$name}' is not a valid package name.");
        }

        return $name;
    }

    protected function resolvePackagePath()
    {
        $path = $this->base . '/' . 'vendor' . '/' . $this->packageName;

        if (! $this->files->exists($path)) {
            throw new Exception("The package '{$this->packageName}' could not be found. \nRun 'composer require {$this->packageName}' first.");
        }

        return $path;
    }
}
