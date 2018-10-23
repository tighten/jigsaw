<?php

namespace TightenCo\Jigsaw\Scaffold;

use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\Scaffold\ProcessRunner;

class PresetScaffoldBuilder extends ScaffoldBuilder
{
    public $package;
    protected $files;
    protected $process;
    protected $question;
    protected $composerCache;

    public function __construct(Filesystem $files, PresetPackage $package, ProcessRunner $process)
    {
        $this->files = $files;
        $this->package = $package;
        $this->process = $process;
        $this->setBase();
    }

    public function init($preset)
    {
        $this->package->init($preset, $this);
        $this->addPackageToCachedComposerRequires();

        return $this;
    }

    public function build()
    {
        $this->package->runInstaller($this->console);

        return $this;
    }

    public function buildBasicScaffold()
    {
        (new BasicScaffoldBuilder($this->files))
            ->setBase($this->base)
            ->build();

        return $this;
    }

    public function cacheComposerDotJson()
    {
        if ($composer = $this->getComposer()) {
            $this->composerCache = array_get($composer, 'require');
        }

        return $this;
    }

    public function mergeComposerDotJson()
    {
        if ($this->composerCache) {
            if ($newComposer = $this->getComposer()) {
                $newComposer['require'] = array_merge(array_get($newComposer, 'require', []), $this->composerCache);
            } else {
                $newComposer = ['require' => $this->composerCache];
            }

            $this->writeComposer($newComposer);
        }

        return $this;
    }

    public function deleteSiteFiles($match = [])
    {
        if (collect($match)->count()) {
            collect($this->getSiteFilesAndDirectories($match))
                ->each(function ($file) {
                    $source = $file->getPathName();

                    if ($this->files->isDirectory($file)) {
                        $this->files->deleteDirectory($source);
                    } else if ($this->files->isFile($file)) {
                        $this->files->delete($source);
                    }
                });
        }

        return $this;
    }

    public function copyPresetFiles($match = [], $ignore = [], $directory = null)
    {
        $source = $this->package->path .
            ($directory ? DIRECTORY_SEPARATOR . trim($directory, '/') : '');

        collect($this->getPresetDirectories($match, $ignore, $source))
            ->each(function ($directory) {
                $destination = $this->base . DIRECTORY_SEPARATOR . $directory->getRelativePathName();

                if (! $this->files->exists($destination)) {
                    $this->files->makeDirectory($destination, 0755, true);
                }
            });

        collect($this->getPresetFiles($match, $ignore, $source))
            ->each(function ($file) {
                $source = $file->getPathName();
                $destination = $this->base . DIRECTORY_SEPARATOR . $file->getRelativePathName();
                $this->files->copy($source, $destination);
            });

        return $this;
    }

    public function runCommands($commands = [])
    {
        $this->process->run($commands);

        return $this;
    }

    protected function addPackageToCachedComposerRequires()
    {
        $this->composerDependencies[] = $this->package->vendor . DIRECTORY_SEPARATOR . $this->package->name;
    }

    protected function createDirectoryForFile($file)
    {
        $path = $file->getRelativePath();

        if ( $path && ! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
    }

    protected function getSiteFilesAndDirectories($match = [], $ignore = [])
    {
        return $this->files->filesAndDirectories($this->base, $match, $ignore);
    }

    protected function getPresetDirectories($match = [], $ignore = [], $source)
    {
        return $this->files->directories($source, $match, $ignore);
    }

    protected function getPresetFiles($match = [], $ignore = [], $source)
    {
        return $this->files->files($source, $match, $ignore);
    }
}
