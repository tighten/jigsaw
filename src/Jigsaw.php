<?php namespace Jigsaw\Jigsaw;

use Illuminate\Contracts\View\Factory;
use Illuminate\Filesystem\Filesystem;

class Jigsaw
{
    private $files;
    private $cachePath;
    private $handlers = [];

    public function __construct(Filesystem $files, $cachePath)
    {
        $this->files = $files;
        $this->cachePath = $cachePath;
    }

    public function registerHandler($handler)
    {
        $this->handlers[] = $handler;
    }

    public function build($source, $dest)
    {
        $this->prepareDirectories([$this->cachePath, $dest]);
        $this->buildSite($source, $dest);
        $this->cleanup();
    }

    private function prepareDirectories($directories)
    {
        foreach ($directories as $directory) {
            $this->prepareDirectory($directory, true);
        }
    }

    private function prepareDirectory($directory, $clean = false)
    {
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory);
        }

        if ($clean) {
            $this->files->cleanDirectory($directory);
        }
    }

    private function buildSite($source, $dest)
    {
        collect($this->files->allFiles($source))->filter(function ($file) {
            return ! $this->shouldIgnore($file);
        })->each(function ($file) use ($dest) {
            $this->buildFile($file, $dest);
        });
    }

    private function cleanup()
    {
        $this->files->deleteDirectory($this->cachePath);
    }

    private function shouldIgnore($file)
    {
        return preg_match('/(^_|\/_)/', $file->getRelativePathname()) === 1;
    }

    private function buildFile($file, $dest)
    {
        $this->prepareDirectory($dest . '/' . $file->getRelativePath());
        $processedFile = $this->handle($file);
        $this->files->put("{$dest}/{$processedFile->relativePathname()}", $processedFile->contents());
    }

    private function handle($file)
    {
        return $this->getHandler($file)->handle($file);
    }

    private function getHandler($file)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($file)) {
                return $handler;
            }
        }
    }
}
