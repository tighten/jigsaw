<?php namespace TightenCo\Jigsaw;

use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\File\InputFile;

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

    public function build($source, $dest, $siteData)
    {
        $this->prepareDirectories([$this->cachePath, $dest]);
        $outputFiles = $this->writeFiles($source, $dest, $siteData);
        $this->cleanup();

        return $outputFiles;
    }

    public function registerHandler($handler)
    {
        $this->handlers[] = $handler;
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

    private function writeFiles($source, $destination, $siteData)
    {
        return collect($this->files->allFiles($source))->map(function ($file) use ($source) {
            return new InputFile($file, $source);
        })->flatMap(function ($file) use ($siteData) {
            return $this->handle($file, $siteData);
        })->map(function ($file) use ($destination) {
            return $this->writeFile($file, $destination);
        });
    }

    private function handle($file, $siteData)
    {
        $meta = $this->getMetaData($file, $siteData->page->baseUrl);

        return $this->getHandler($file)->handle($file, PageData::withPageMetaData($siteData, $meta));
    }

    private function writeFile($file, $dest)
    {
        $directory = $this->getOutputDirectory($file);
        $this->prepareDirectory("{$dest}/{$directory}");
        $file->putContents("{$dest}/{$this->getOutputPath($file)}");

        return $this->getOutputLink($file);
    }

    private function getHandler($file)
    {
        return collect($this->handlers)->first(function ($handler) use ($file) {
            return $handler->shouldHandle($file);
        });
    }

    private function getMetaData($file, $baseUrl)
    {
        $filename = $file->getFilenameWithoutExtension();
        $extension = $file->getFullExtension();
        $path = rightTrimPath($this->outputPathResolver->link($file->getRelativePath(), $filename, $file->getExtraBladeExtension() ?: 'html'));
        $url = rightTrimPath($baseUrl) . '/' . trimPath($path);

        return compact('filename', 'baseUrl', 'path', 'extension', 'url');
    }

    private function getOutputDirectory($file)
    {
        if ($permalink = $this->getFilePermalink($file)) {
            return urldecode(dirname($permalink));
        }

        return urldecode($this->outputPathResolver->directory($file->path(), $file->name(), $file->extension(), $file->page()));
    }

    private function getOutputPath($file)
    {
        if ($permalink = $this->getFilePermalink($file)) {
            return $permalink;
        }

        return resolvePath(urldecode($this->outputPathResolver->path(
            $file->path(), $file->name(), $file->extension(), $file->page()
        )));
    }

    private function getOutputLink($file)
    {
        if ($permalink = $this->getFilePermalink($file)) {
            return $permalink;
        }

        return rightTrimPath(urldecode($this->outputPathResolver->link(
            $file->path(), $file->name(), $file->extension(), $file->page()
        )));
    }

    private function getFilePermalink($file)
    {
        return $file->data()->page->permalink ? resolvePath(urldecode($file->data()->page->permalink)) : NULL;
    }
}
