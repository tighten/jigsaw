<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw;

use Illuminate\Support\Collection;
use Symfony\Component\Finder\SplFileInfo;
use TightenCo\Jigsaw\Console\ConsoleOutput;
use TightenCo\Jigsaw\Contracts\ItemHandler;
use TightenCo\Jigsaw\Contracts\PathResolver;
use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\File\InputFile;
use TightenCo\Jigsaw\File\OutputFile;

class SiteBuilder
{
    /** @var string */
    private $cachePath;

    /** @var Filesystem */
    private $files;

    /** @var ItemHandler[] */
    private $handlers;

    /** @var PathResolver */
    private $outputPathResolver;

    /** @var ConsoleOutput */
    private $consoleOutput;

    /** @var bool */
    private $useCache;

    /**
     * @param ItemHandler[] $handlers
     */
    public function __construct(Filesystem $files, string $cachePath, PathResolver $outputPathResolver, ConsoleOutput $consoleOutput, array $handlers = [])
    {
        $this->files = $files;
        $this->cachePath = $cachePath;
        $this->outputPathResolver = $outputPathResolver;
        $this->consoleOutput = $consoleOutput;
        $this->handlers = $handlers;
    }

    public function setUseCache(bool $useCache): SiteBuilder
    {
        $this->useCache = $useCache;

        return $this;
    }

    public function build(string $source, string $destination, SiteData $siteData): Collection
    {
        $this->prepareDirectory($this->cachePath, ! $this->useCache);
        $generatedFiles = $this->generateFiles($source, $siteData);
        $this->prepareDirectory($destination);
        $outputFiles = $this->writeFiles($generatedFiles, $destination);
        $this->cleanup();

        return $outputFiles;
    }

    public function registerHandler(ItemHandler $handler): void
    {
        $this->handlers[] = $handler;
    }

    /**
     * @param string[] $directories
     */
    private function prepareDirectories(array $directories): void
    {
        foreach ($directories as $directory) {
            $this->prepareDirectory($directory, true);
        }
    }

    private function prepareDirectory(string $directory, bool $clean = false): void
    {
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        if ($clean) {
            $this->files->cleanDirectory($directory);
        }
    }

    private function cleanup(): void
    {
        if (! $this->useCache) {
            $this->files->deleteDirectory($this->cachePath);
        }
    }

    private function generateFiles(string $source, SiteData $siteData): Collection
    {
        $files = collect($this->files->allFiles($source));
        $this->consoleOutput->startProgressBar('build', $files->count());

        $files = $files->map(function (SplFileInfo $file): InputFile {
            return new InputFile($file);
        })->flatMap(function (InputFile $file) use ($siteData): Collection {
            $this->consoleOutput->progressBar('build')->advance();

            return $this->handle($file, $siteData);
        });

        return $files;
    }

    private function writeFiles(Collection $files, $destination): Collection
    {
        $this->consoleOutput->writeWritingFiles();

        return $files->map(function (OutputFile $file) use ($destination): string {
            return $this->writeFile($file, $destination);
        });
    }

    private function writeFile(OutputFile $file, $destination): string
    {
        $directory = $this->getOutputDirectory($file);
        $this->prepareDirectory("{$destination}/{$directory}");
        $file->putContents("{$destination}/{$this->getOutputPath($file)}");

        return $this->getOutputLink($file);
    }

    private function handle(InputFile $file, SiteData $siteData): Collection
    {
        $meta = $this->getMetaData($file, $siteData->page->baseUrl ?? 'y');

        return $this->getHandler($file)->handle($file, PageData::withPageMetaData($siteData, $meta));
    }

    private function getHandler(InputFile $file): ?ItemHandler
    {
        return collect($this->handlers)->first(function (ItemHandler $handler) use ($file): bool {
            return $handler->shouldHandle($file);
        });
    }

    private function getMetaData(InputFile $file, string $baseUrl): array
    {
        $filename = $file->getFilenameWithoutExtension();
        $extension = $file->getFullExtension();
        $path = rightTrimPath($this->outputPathResolver->link($file->getRelativePath(), $filename, $file->getExtraBladeExtension() ?: 'html'));
        $url = rightTrimPath($baseUrl) . '/' . trimPath($path);

        return compact('filename', 'baseUrl', 'path', 'extension', 'url');
    }

    private function getOutputDirectory(OutputFile $file): string
    {
        if ($permalink = $this->getFilePermalink($file)) {
            return urldecode(dirname($permalink));
        }

        return urldecode($this->outputPathResolver->directory($file->path(), $file->name(), $file->extension(), $file->page()));
    }

    private function getOutputPath(OutputFile $file): string
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

    private function getOutputLink(OutputFile $file): string
    {
        if ($permalink = $this->getFilePermalink($file)) {
            return $permalink;
        }

        return rightTrimPath(urldecode($this->outputPathResolver->link(
            $file->path(),
            $file->name(),
            $file->extension(),
            $file->page()
        )));
    }

    private function getFilePermalink(OutputFile $file): ?string
    {
        return $file->data()->page->permalink ? resolvePath(urldecode($file->data()->page->permalink)) : null;
    }
}
