<?php namespace TightenCo\Jigsaw;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Str;
use TightenCo\Jigsaw\Filesystem;

class Jigsaw
{
    private $source;
    private $dest;
    private $config;
    private $collections;
    private $files;
    private $cachePath;
    private $handlers;
    private $options = [
        'pretty' => true
    ];

    public function __construct($source, $dest, $config, $collections, Filesystem $files, $cachePath, $handlers = [])
    {
        $this->source = $source;
        $this->dest = $dest;
        $this->config = $config;
        $this->collections = $collections;
        $this->files = $files;
        $this->cachePath = $cachePath;
        $this->handlers = $handlers;
    }

    public function registerHandler($handler)
    {
        $this->handlers[] = $handler;
    }

    public function build()
    {
        $this->prepareDirectories([$this->cachePath, $this->dest]);
        $this->buildCollections();
        $this->buildSite();
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

    private function buildCollections()
    {
        foreach ($this->collections as $name => $settings) {
            $this->buildCollection($name, $settings);
        }
    }

    private function buildCollection($name, $settings)
    {
        $path = "{$this->source}/_{$name}";

        collect($this->files->allFiles($path))->map(function ($file) use ($settings) {
            return new ProcessedCollectionFile($this->handle($file), $settings);
        })->each(function ($file) {
            $this->buildFile($file);
        });
    }

    private function buildSite()
    {
        collect($this->files->allFiles($this->source))->filter(function ($file) {
            return ! $this->shouldIgnore($file);
        })->map(function ($file) {
            return $this->handle($file);
        })->each(function ($file) {
            $this->buildFile($file);
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

    private function buildFile($file)
    {
        $directory = $this->getDirectory($file);
        $this->prepareDirectory("{$this->dest}/{$directory}");
        $this->files->put("{$this->dest}/{$this->getRelativePathname($file)}", $file->contents());
    }

    private function handle($file)
    {
        return $this->getHandler($file)->handle($file, $this->config);
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
        return collect($this->handlers)->first(function ($_, $handler) use ($file) {
            return $handler->canHandle($file);
        });
    }
}
