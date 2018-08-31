<?php

namespace TightenCo\Jigsaw\Scaffold;

use TightenCo\Jigsaw\File\Filesystem;

abstract class Scaffold
{
    const ADDITIONAL_FILES_TO_RESET = [
        'composer.json',
        'composer.lock',
        'package.lock',
        'yarn.lock',
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
            })->merge(self::ADDITIONAL_FILES_TO_RESET)
            ->flatten()
            ->unique();
    }

    public function archiveExistingSite()
    {
        $version = $this->getJigsawComposerVersion();

        $archivePath = $this->base . '/archived';
        $this->files->deleteDirectory($archivePath);
        $this->files->makeDirectory($archivePath, 0755, true);

        collect($this->getSiteFiles())->each(function ($file) use ($archivePath) {
            $existingPath = $this->base . '/' . $file;

            if ($this->files->exists($existingPath)) {
                $this->files->move($existingPath, $archivePath . '/' . ltrim($file, '/'));
            }
        });

        $this->restoreJigsawComposerFile($version);
    }

    public function deleteExistingSite()
    {
        $version = $this->getJigsawComposerVersion();

        collect($this->getSiteFiles())->each(function ($file) {
            $existingPath = $this->base . '/' . $file;

            if ($this->files->isDirectory($existingPath)) {
                $this->files->deleteDirectory($existingPath);
            } else {
                $this->files->delete($existingPath);
            }
        });

        $this->restoreJigsawComposerFile($version);
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
