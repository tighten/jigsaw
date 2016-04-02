<?php namespace TightenCo\Jigsaw;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Str;
use TightenCo\Jigsaw\Filesystem;

class Jigsaw
{
    private $files;
    private $cachePath;
    private $handlers = [];
    private $options = [
        'pretty' => true
    ];

    public function __construct(Filesystem $files, $cachePath)
    {
        $this->files = $files;
        $this->cachePath = $cachePath;
    }

    public function registerHandler($handler)
    {
        $this->handlers[] = $handler;
    }

    public function build($source, $dest, $config = [])
    {
        $this->prepareDirectories([$this->cachePath, $dest]);
        $this->buildSite($source, $dest, $config);
        $this->cleanup();
    }

    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
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
            $this->files->makeDirectory($directory, 0755, true);
        }

        if ($clean) {
            $this->files->cleanDirectory($directory);
        }
    }

    private function buildSite($source, $dest, $config)
    {
        collect($this->files->allFiles($source))->filter(function ($file) {
            return ! $this->shouldIgnore($file);
        })->each(function ($file) use ($dest, $config) {
            $this->buildFile($file, $dest, $config);
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

    private function buildFile($file, $dest, $config)
    {
        $file = $this->handle($file, $config);
        $directory = $this->getDirectory($file);
        $this->prepareDirectory("{$dest}/{$directory}");
        $this->files->put("{$dest}/{$this->getRelativePathname($file)}", $file->contents());
    }

    private function handle($file, $config)
    {
        return $this->getHandler($file)->handle($file, $config);
    }

    private function getDirectory($file)
    {
        if ($this->options['pretty']) {
            return $this->getPrettyDirectory($file);
        }

        return $file->relativePath();
    }

    private function getPrettyDirectory($file)
    {
        if ($file->extension() === 'html' && $file->name() !== 'index.html') {
            return "{$file->relativePath()}/{$file->basename()}";
        }

        return $file->relativePath();
    }

    private function getRelativePathname($file)
    {
        if ($this->options['pretty']) {
            return $this->getPrettyRelativePathname($file);
        }

        return $file->relativePathname();
    }

    private function getPrettyRelativePathname($file)
    {
        if ($file->extension() === 'html' && $file->name() !== 'index.html') {
            return $this->getPrettyDirectory($file) . '/index.html';
        }

        return $file->relativePathname();
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
