<?php

namespace TightenCo\Jigsaw\Scaffold;

use TightenCo\Jigsaw\File\Filesystem;

abstract class Scaffold
{
    const BASE_SITE_FILES = [
        '.gitignore',
        'bootstrap.php',
        'config.php',
        'source/',
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

    public function getSiteFiles()
    {
        return collect(['site', 'elixir', 'mix'])
            ->map(function ($stub) {
                return $this->getFilesAndDirectories(__DIR__ . '/../../stubs/' . $stub);
            })->flatten()->unique();
    }

    public function archiveExistingSite()
    {
        $archivePath = $this->base . '/archived';
        $this->files->deleteDirectory($archivePath);
        $this->files->makeDirectory($archivePath, 0755, true);

        collect($this->getSiteFiles())->each(function ($file) use ($archivePath) {
            $existingPath = $this->base . '/' . $file;

            if ($this->files->exists($existingPath)) {
                $this->files->move($existingPath, $archivePath . '/' . ltrim($file, '/'));
            }
        });
    }

    public function deleteExistingSite()
    {
        collect($this->getSiteFiles())->each(function ($file) {
            $existingPath = $this->base . '/' . $file;

            if ($this->files->isDirectory($existingPath)) {
                $this->files->deleteDirectory($existingPath);
            } else {
                $this->files->delete($existingPath);
            }
        });
    }

    protected function getFilesAndDirectories($directory)
    {
        $directories = collect($this->files->directories($directory))
            ->map(function ($path) {
                return basename($path) . '/';
            });

        return collect($this->files->files($directory, true))
            ->map(function ($file) {
                return $file->getFileName();
            })->reject(function ($filename) {
                return $filename == '.DS_Store';
            })->merge($directories);
    }
}
