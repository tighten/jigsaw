<?php

namespace TightenCo\Jigsaw\Scaffold;

use TightenCo\Jigsaw\File\Filesystem;

abstract class ScaffoldBuilder
{
    const IGNORE_DIRECTORIES = [
        'archived',
        'node_modules',
        'vendor',
    ];

    public $base;
    protected $files;
    protected $composerDependencies = ['tightenco/jigsaw'];

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        $this->setBase();
    }

    abstract public function init($preset);

    abstract public function build();

    public function setBase($cwd = null)
    {
        $this->base = $cwd ?: getcwd();

        return $this;
    }

    public function archiveExistingSite()
    {
        $versions = $this->getComposerRequireVersions();
        $this->createEmptyArchive();

        collect($this->allBaseFiles())->each(function ($file) use (&$directories) {
            $source = $file->getPathName();
            $destination = $this->base . '/archived/' . $file->getRelativePathName();

            if ($this->files->isDirectory($file)) {
                $directories[] = $source;
                $this->files->makeDirectory($destination, 0755, true);
            } else {
                $this->files->move($source, $destination);
            }
        });

        $this->deleteEmptyDirectories($directories);
        $this->restoreComposer($versions);
    }

    public function deleteExistingSite()
    {
        $versions = $this->getComposerRequireVersions();

        collect($this->allBaseFiles())->each(function ($file) use (&$directories) {
            $source = $file->getPathName();

            if ($this->files->isDirectory($file)) {
                $directories[] = $source;
            } else {
                $this->files->delete($source);
            }
        });

        $this->deleteEmptyDirectories($directories);
        $this->restoreComposer($versions);
    }

    protected function createEmptyArchive()
    {
        $this->files->deleteDirectory($this->base . '/archived');
        $this->files->makeDirectory($this->base . '/archived', 0755, true);
    }

    protected function deleteEmptyDirectories($directories)
    {
        collect($directories)->each(function ($directory) {
            if ($this->files->isEmptyDirectory($directory)) {
                $this->files->deleteDirectory($directory);
            };
        });
    }

    protected function allBaseFiles()
    {
        return $this->files->filesAndDirectories(
            $this->base,
            null,
            self::IGNORE_DIRECTORIES,
            $ignore_dotfiles = false
        );
    }

    protected function getComposerRequireVersions()
    {
        if (! $this->files->exists($this->base . '/composer.json')) {
            return;
        }

        $composer = json_decode($this->files->get($this->base . '/composer.json'), true);

        return collect($this->composerDependencies)
            ->mapWithKeys(function ($dependency) use ($composer) {
                return [$dependency => array_get($composer, 'require.' . $dependency)];
            })->filter();
    }

    protected function restoreComposer($versions = null)
    {
        if (! $versions) {
            return;
        }

        $this->files->put(
            $this->base . '/composer.json',
            json_encode(
                ['require' => $versions->toArray()],
                JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT
            )
        );
    }
}
