<?php namespace TightenCo\Jigsaw;

class SiteBuilder
{
    private $files;
    private $cachePath;
    private $handlers;
    private $options = [
        'pretty' => true
    ];

    public function __construct(Filesystem $files, $cachePath, $handlers = [], $options = [])
    {
        $this->files = $files;
        $this->cachePath = $cachePath;
        $this->handlers = $handlers;
        $this->options = array_merge($this->options, $options);
    }

    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    public function registerHandler($handler)
    {
        $this->handlers[] = $handler;
    }

    public function build($source, $dest, $data, $options = [])
    {
        $this->options = array_merge($this->options, $options);
        $this->prepareDirectories([$this->cachePath, $dest]);
        $this->buildSite($source, $dest, $data);
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

    private function buildSite($source, $dest, $data)
    {
        $result = collect($this->files->allFiles($source))->map(function ($file) use ($source) {
            return new InputFile($file, $source);
        })->flatMap(function ($file) use ($data) {
            return $this->handle($file, $data);
        })->each(function ($file) use ($dest) {
            $this->buildFile($file, $dest);
        });
    }

    private function handle($file, $data)
    {
        return $this->getHandler($file)->handle($file, $data);
    }

    private function buildFile($file, $dest)
    {
        $directory = $this->getDirectory($file);
        $this->prepareDirectory("{$dest}/{$directory}");
        $this->files->put("{$dest}/{$this->getRelativePathname($file)}", $file->contents());
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
            return $file->prettyDirectory();
        }

        return $file->relativePath();
    }

    private function getPrettyDirectory($file)
    {
        return $file->prettyDirectory();
    }

    private function getRelativePathname($file)
    {
        if ($this->options['pretty']) {
            return $file->prettyRelativePathname();
        }

        return $file->relativePathname();
    }
}
