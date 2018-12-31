<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Scaffold;

use TightenCo\Jigsaw\Console\ConsoleSession;
use TightenCo\Jigsaw\File\Filesystem;

abstract class ScaffoldBuilder
{
    const IGNORE_DIRECTORIES = [
        'archived',
        'node_modules',
        'vendor',
    ];

    /** @var string */
    public $base;

    /** @var ConsoleSession */
    protected $console;

    /** @var Filesystem */
    protected $files;

    /** @deprecated unused */
    protected $process;

    /** @var array[] */
    protected $composerCache = [];

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        $this->setBase();
    }

    abstract public function init($preset): ScaffoldBuilder;

    abstract public function build(): ScaffoldBuilder;

    public function setBase($cwd = null): ScaffoldBuilder
    {
        $this->base = $cwd ?: getcwd();

        return $this;
    }

    public function setConsole($console): ScaffoldBuilder
    {
        $this->console = $console;

        return $this;
    }

    public function archiveExistingSite(): void
    {
        $this->cacheComposerDotJson();
        $this->createEmptyArchive();

        collect($this->allBaseFiles())->each(function ($file) use (&$directories): void {
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
        $this->restoreComposerDotJson();
    }

    public function deleteExistingSite(): void
    {
        $this->cacheComposerDotJson();

        collect($this->allBaseFiles())->each(function ($file) use (&$directories): void {
            $source = $file->getPathName();

            if ($this->files->isDirectory($file)) {
                $directories[] = $source;
            } else {
                $this->files->delete($source);
            }
        });

        $this->deleteEmptyDirectories($directories);
        $this->restoreComposerDotJson();
    }

    public function cacheComposerDotJson(): ScaffoldBuilder
    {
        $this->composerCache = $this->getComposer() ?? [];

        return $this;
    }

    public function restoreComposerDotJson(): void
    {
        $composer = collect($this->composerCache)->only(['require', 'repositories']);

        if ($composer->count() && $jigsaw_require = collect($composer->get('require'))->only('tightenco/jigsaw')) {
            $this->writeComposer($composer->put('require', $jigsaw_require));
        }
    }

    protected function createEmptyArchive(): void
    {
        $archived = $this->base . DIRECTORY_SEPARATOR . 'archived';
        $this->files->deleteDirectory($archived);
        $this->files->makeDirectory($archived, 0755, true);
    }

    protected function deleteEmptyDirectories($directories): void
    {
        collect($directories)->each(function ($directory): void {
            if ($this->files->isEmptyDirectory($directory)) {
                $this->files->deleteDirectory($directory);
            }
        });
    }

    protected function allBaseFiles(): array
    {
        return $this->files->filesAndDirectories(
            $this->base,
            null,
            self::IGNORE_DIRECTORIES,
            $ignore_dotfiles = false
        );
    }

    protected function getComposer(): ?array
    {
        $composer = $this->base . DIRECTORY_SEPARATOR . 'composer.json';

        if ($this->files->exists($composer)) {
            return json_decode($this->files->get($composer), true);
        }

        return null;
    }

    protected function writeComposer($content = null): void
    {
        if ($content) {
            $this->files->put(
                $this->base . DIRECTORY_SEPARATOR . 'composer.json',
                json_encode($content, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            );
        }
    }
}
