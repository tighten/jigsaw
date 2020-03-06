<?php

namespace TightenCo\Jigsaw;

use TightenCo\Jigsaw\File\InputFile;
use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\Console\ConsoleOutput;

class SiteBuilder
{
    private $cachePath;
    private $files;
    private $handlers;
    private $outputPathResolver;
    private $consoleOutput;
    private $useCache;

    public function __construct(
        Filesystem $files,
        $cachePath,
        $outputPathResolver,
        ConsoleOutput $consoleOutput,
        $handlers = []
    ) {
        $this->files = $files;
        $this->cachePath = $cachePath;
        $this->outputPathResolver = $outputPathResolver;
        $this->consoleOutput = $consoleOutput;
        $this->handlers = $handlers;
    }

    public function setUseCache($useCache)
    {
        $this->useCache = $useCache;

        return $this;
    }

    public function build($source, $destination, $siteData)
    {
        $this->prepareDirectory($this->cachePath, ! $this->useCache);
        $generatedFiles = $this->generateFiles($source, $siteData);
        $this->prepareDirectory($destination, true);
        $outputFiles = $this->writeFiles($generatedFiles, $destination);
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
        if (! $this->useCache) {
            $this->files->deleteDirectory($this->cachePath);
        }
    }

    private function generateFiles($source, $siteData)
    {
        $files = collect($this->files->files($source));
        $this->consoleOutput->startProgressBar('build', $files->count());

        $files = $files->map(function ($file) {
            return new InputFile($file);
        })->flatMap(function ($file) use ($siteData) {
            $this->consoleOutput->progressBar('build')->advance();

            return $this->handle($file, $siteData);
        });

        return $files;
    }

    private function writeFiles($files, $destination)
    {
        $this->consoleOutput->writeWritingFiles();

        return $files->mapWithKeys(function ($file) use ($destination) {
            $outputLink = $this->writeFile($file, $destination);

            return [$outputLink => $file->inputFile()->getPageData()];
        });
    }

    private function writeFile($file, $destination)
    {
        $directory = $this->getOutputDirectory($file);
        $this->prepareDirectory("{$destination}/{$directory}");
        $file->putContents("{$destination}/{$this->getOutputPath($file)}");

        return $this->getOutputLink($file);
    }

    private function handle($file, $siteData)
    {
        $meta = $this->getMetaData($file, $siteData->page->baseUrl);

        return $this->getHandler($file)->handle($file, PageData::withPageMetaData($siteData, $meta));
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
        $relativePath = $file->getRelativePath();
        $url = rightTrimPath($baseUrl) . '/' . trimPath($path);
        $modifiedTime = $file->getLastModifiedTime();

        return compact('filename', 'baseUrl', 'path', 'relativePath', 'extension', 'url', 'modifiedTime');
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
            $file->path(),
            $file->name(),
            $file->extension(),
            $file->page()
        )));
    }

    private function getOutputLink($file)
    {
        if ($permalink = $this->getFilePermalink($file)) {
            return $permalink;
        }

        return rightTrimPath(urldecode($this->outputPathResolver->link(
            str_replace('\\', '/', $file->path()),
            $file->name(),
            $file->extension(),
            $file->page()
        )));
    }

    private function getFilePermalink($file)
    {
        return $file->data()->page->permalink ? '/' . resolvePath(urldecode($file->data()->page->permalink)) : null;
    }
}
