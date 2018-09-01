<?php

namespace TightenCo\Jigsaw\Scaffold;

use TightenCo\Jigsaw\File\Filesystem;

abstract class Scaffold
{
    const IGNORE_DIRECTORIES = [
        'archived',
        'node_modules',
        'vendor',
    ];

    public $base;
    protected $files;

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
        $version = $this->getJigsawComposerVersion();
        $this->createEmptyArchive();

        collect($this->allFiles())->each(function ($file) use (&$directories) {
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
        $this->restoreJigsawComposerFile($version);
    }

    public function deleteExistingSite()
    {
        $version = $this->getJigsawComposerVersion();

        collect($this->allFiles())->each(function ($file) use (&$directories) {
            $source = $file->getPathName();

            if ($this->files->isDirectory($file)) {
                $directories[] = $source;
            } else {
                $this->files->delete($source);
            }
        });

        $this->deleteEmptyDirectories($directories);
        $this->restoreJigsawComposerFile($version);
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

    protected function allFiles()
    {
        return $this->files->allFilesAndDirectories(
            $this->base,
            $ignore_dotfiles = false,
            self::IGNORE_DIRECTORIES
        );
    }

    protected function getJigsawComposerVersion()
    {
        if ($this->files->exists($this->base . '/composer.json')) {
            return array_get(
                json_decode($this->files->get($this->base . '/composer.json'), true),
                'require.tightenco/jigsaw'
            );
        }
    }

    protected function restoreJigsawComposerFile($version = null)
    {
        if ($version) {
            $this->files->put(
                $this->base . '/composer.json',
                json_encode(
                    ['require' => ['tightenco/jigsaw' => $version]],
                    JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT
                )
            );
        }
    }
}
