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

    public $path;

    public function build($preset)
    {
        $this->path = $this->resolvePackage($preset);
    }

    protected function getPackageName($preset)
    {
        return array_get(self::PRESETS, $preset, $preset);
    }

    protected function getPackagePath($package)
    {
        return $this->base . '/' . 'vendor' . '/' . $package;
    }

    protected function resolvePackage($preset)
    {
        $package = $this->getPackageName($preset);
        $path = $this->getPackagePath($package);

        if (! str_contains($package, '/')) {
            throw new Exception("'{$package}' is not a valid package name.");
        }

        if (! $this->files->exists($path)) {
            throw new Exception("The package '{$package}' could not be found. \nRun 'composer require {$package}' first.");
        }

        return $path;
    }
}
