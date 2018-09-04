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
    protected $process;
    protected $composerDependencies = [
        'tightenco/jigsaw',
    ];

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
        $requires = $this->getComposerRequires();
        $this->createEmptyArchive();

        collect($this->allBaseFiles())->each(function ($file) use (&$directories) {
            $source = $file->getPathName();
            $destination = $this->base . DIRECTORY_SEPARATOR . 'archived' . DIRECTORY_SEPARATOR . $file->getRelativePathName();

            if ($this->files->isDirectory($file)) {
                $directories[] = $source;
                $this->files->makeDirectory($destination, 0755, true);
            } else {
                $this->files->move($source, $destination);
            }
        });

        $this->deleteEmptyDirectories($directories);

        if ($requires) {
            $this->writeComposer(['require' => $requires]);
        }
    }

    public function deleteExistingSite()
    {
        $requires = $this->getComposerRequires();

        collect($this->allBaseFiles())->each(function ($file) use (&$directories) {
            $source = $file->getPathName();

            if ($this->files->isDirectory($file)) {
                $directories[] = $source;
            } else {
                $this->files->delete($source);
            }
        });

        $this->deleteEmptyDirectories($directories);

        if ($requires) {
            $this->writeComposer(['require' => $requires]);
        }
    }

    protected function createEmptyArchive()
    {
        $archived = $this->base . DIRECTORY_SEPARATOR . 'archived';
        $this->files->deleteDirectory($archived);
        $this->files->makeDirectory($archived, 0755, true);
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

    protected function getComposerRequires()
    {
        if ($composer = $this->getComposer()) {
            return collect($this->composerDependencies)
                ->mapWithKeys(function ($dependency) use ($composer) {
                    return [$dependency => array_get($composer, 'require.' . $dependency)];
                })->filter()->toArray();
        }
    }

    protected function getComposer()
    {
        $composer = $this->base . DIRECTORY_SEPARATOR . 'composer.json';

        if ($this->files->exists($composer)) {
            return json_decode($this->files->get($composer), true);
        }
    }

    protected function writeComposer($content = null)
    {
        if ($content) {
            $this->files->put(
                $this->base . DIRECTORY_SEPARATOR . 'composer.json',
                json_encode($content, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT)
            );
        }
    }
}
