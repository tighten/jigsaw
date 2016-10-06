<?php namespace TightenCo\Jigsaw;

class SiteBuilder
{
    private $files;
    private $cachePath;
    private $outputPathResolver;
    private $handlers;

    public function __construct(Filesystem $files, $cachePath, $outputPathResolver, $handlers = [])
    {
        $this->files = $files;
        $this->cachePath = $cachePath;
        $this->outputPathResolver = $outputPathResolver;
        $this->handlers = $handlers;
    }

    public function registerHandler($handler)
    {
        $this->handlers[] = $handler;
    }

    public function build($source, $dest, $data)
    {
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
        $meta = $this->getMetaData($file, $data);

        return $this->getHandler($file)->handle($file, $this->addMetaToPageData($meta, $data));
    }

    private function buildFile($file, $dest)
    {
        $directory = $this->getOutputDirectory($file);
        $this->prepareDirectory("{$dest}/{$directory}");
        $this->files->put("{$dest}/{$this->getOutputPath($file)}", $file->contents());
    }

    private function getHandler($file)
    {
        return collect($this->handlers)->first(function ($handler) use ($file) {
            return $handler->shouldHandle($file);
        });
    }

    private function getMetaData($file, $data)
    {
        $meta['filename'] = $file->getFilenameWithoutExtension();
        $meta['extension'] = $file->getFullExtension();
        $meta['path'] = rtrim($this->outputPathResolver->link($file->getRelativePath(), $meta['filename'], 'html'), '/');
        $meta['url'] = rtrim(array_get($data, 'config.baseUrl'), '/') . '/' . trim($meta['path'], '/');

        return $meta;
    }

    private function addMetaToPageData($meta, $data)
    {
        return $data->put('config', $data->get('config')->merge($meta))->merge($meta);
    }

    private function getOutputDirectory($file)
    {
        return $this->outputPathResolver->directory($file->path(), $file->name(), $file->extension(), $file->page());
    }

    private function getOutputPath($file)
    {
        return $this->outputPathResolver->path($file->path(), $file->name(), $file->extension(), $file->page());
    }
}
