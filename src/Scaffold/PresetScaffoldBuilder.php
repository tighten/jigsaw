<?php

namespace TightenCo\Jigsaw\Scaffold;

use Exception;
use TightenCo\Jigsaw\File\Filesystem;

class PresetScaffoldBuilder extends ScaffoldBuilder
{
    public $package;
    protected $files;

    public function __construct(Filesystem $files, PresetPackage $package)
    {
        $this->files = $files;
        $this->package = $package;
        $this->setBase();
    }

    public function init($preset)
    {
        $this->package->init($preset, $this);
        $this->addComposerDependency();

        return $this;
    }

    public function build()
    {
        $this->package->runInstaller();

        return $this;
    }

    public function buildBasicScaffold()
    {
        (new BasicScaffoldBuilder($this->files))
            ->setBase($this->base)
            ->build();

        return $this;
    }

    public function deleteSiteFiles($files = [])
    {
        return $this;
    }

    public function copyPresetFiles($ignore = [])
    {
        // collect($this->allPresetFiles($ignore))
        //     ->each(function ($file) {
        //         $source = $file->getPathName();
        //         $destination = $this->base . '/archived/' . $file->getRelativePathName();

        //         if ($this->files->isDirectory($file)) {
        //             $this->files->makeDirectory($destination, 0755, true);
        //         } else {
        //             $this->files->copy($source, $destination);
        //         }
        //     });

        return $this;
    }

    public function runCommands($commands = [])
    {
        return $this;
    }

    protected function addComposerDependency()
    {
        $this->composerDependencies[] = $this->package->name . '/' . $this->package->name;
    }

    protected function createDirectoryForFile($file)
    {
        $path = $file->getRelativePath();

        if ( $path && ! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
    }

    protected function allPresetFiles($ignore = [])
    {
        return $this->files->filesAndDirectories(
            $this->package->path,
            null,
            array_merge($ignore),
            $ignore_dotfiles = false
        );
    }
}
