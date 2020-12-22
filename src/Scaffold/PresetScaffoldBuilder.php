<?php

namespace TightenCo\Jigsaw\Scaffold;

use Illuminate\Support\Arr;
use TightenCo\Jigsaw\File\Filesystem;

class PresetScaffoldBuilder extends ScaffoldBuilder
{
    public $package;
    protected $files;
    protected $process;
    protected $question;

    public function __construct(Filesystem $files, PresetPackage $package, ProcessRunner $process)
    {
        parent::__construct($files);

        $this->package = $package;
        $this->process = $process;
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

    public function mergeComposerDotJson()
    {
        $newComposer = collect($this->getComposer())
            ->forget(['name', 'type', 'version', 'description', 'keywords', 'license', 'authors'])
            ->toArray()
            ?? [];
        $merged = array_merge_recursive($this->composerCache ?? [], $newComposer);
        $this->writeComposer($this->preferVersionConstraintFromCached($merged));

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
                    } elseif ($this->files->isFile($file)) {
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

        collect($this->getPresetDirectories($source, $match, $ignore))
            ->each(function ($directory) {
                $destination = $this->base . DIRECTORY_SEPARATOR . $directory->getRelativePathName();

                if (! $this->files->exists($destination)) {
                    $this->files->makeDirectory($destination, 0755, true);
                }
            });

        collect($this->getPresetFiles($source, $match, $ignore))
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

        if ($path && ! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
    }

    protected function getSiteFilesAndDirectories($match = [], $ignore = [])
    {
        return $this->files->filesAndDirectories($this->base, $match, $ignore);
    }

    protected function getPresetDirectories($source, $match = [], $ignore = [])
    {
        return $this->files->directories($source, $match, $ignore);
    }

    protected function getPresetFiles($source, $match = [], $ignore = [])
    {
        return $this->files->files($source, $match, $ignore);
    }

    protected function preferVersionConstraintFromCached($composer)
    {
        $require = collect(Arr::get($composer, 'require'))->mapWithKeys(function ($version, $package) {
            return [$package => is_array($version) ? $version[0] : $version];
        });

        return Arr::set($composer, 'require', $require);
    }
}
