<?php namespace TightenCo\Jigsaw;

class SiteBuilder
{
    private $source;
    private $dest;
    private $config;
    private $files;
    private $cachePath;
    private $handlers;
    private $options = [
        'pretty' => true
    ];

    public function __construct($source, $dest, $data, $options, Filesystem $files, $cachePath, $handlers = [])
    {
        $this->source = $source;
        $this->dest = $dest;
        $this->config = $data;
        $this->options = array_merge($this->options, $options);
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
        $this->buildSite();
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
            $this->files->makeDirectory($directory, 0755, true);
        }

        if ($clean) {
            $this->files->cleanDirectory($directory);
        }
    }

    private function cleanup()
    {
        $this->files->deleteDirectory($this->cachePath);
    }

    private function buildSite()
    {
        $result = collect($this->files->allFiles($this->source))->map(function ($file) {
            return new InputFile($file, $this->source);
        })->flatMap(function ($file) {
            return $this->handle($file);
        })->each(function ($file) {
            $this->buildFile($file);
        });
    }

    private function handle($file)
    {
        return $this->getHandler($file)->handle($file, $this->config);
    }

    private function buildFile($file)
    {
        $directory = $this->getDirectory($file);
        $this->prepareDirectory("{$this->dest}/{$directory}");
        $this->files->put("{$this->dest}/{$this->getRelativePathname($file)}", $file->contents());
    }

    private function getHandler($file)
    {
        return collect($this->handlers)->first(function ($_, $handler) use ($file) {
            return $handler->shouldHandle($file);
        });
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
}
