<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\File;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Filesystem extends BaseFilesystem
{
    public function getFile($directory, $filename): SplFileInfo
    {
        $filePath = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        return new SplFileInfo($filePath, $directory, $filename);
    }

    public function putWithDirectories($file_path, $contents): void
    {
        $directory_path = collect(explode('/', $file_path));
        $directory_path->pop();
        $directory_path = trimPath($directory_path->implode('/'));

        if (! $this->isDirectory($directory_path)) {
            $this->makeDirectory($directory_path, 0755, true);
        }

        $this->put($file_path, $contents);
    }

    public function files($directory, $match = [], $ignore = [], $ignore_dotfiles = false): array
    {
        return $directory ? iterator_to_array(
            $this->getFinder($directory, $match, $ignore, $ignore_dotfiles)->files(),
            false
        ) : [];
    }

    public function directories($directory, $match = [], $ignore = [], $ignore_dotfiles = false): array
    {
        return $directory ? iterator_to_array(
            $this->getFinder($directory, $match, $ignore, $ignore_dotfiles)->directories(),
            false
        ) : [];
    }

    public function filesAndDirectories($directory, $match = [], $ignore = [], $ignore_dotfiles = false): array
    {
        return $directory ? iterator_to_array(
            $this->getFinder($directory, $match, $ignore, $ignore_dotfiles),
            false
        ) : [];
    }

    public function isEmptyDirectory($directory): bool
    {
        return $this->exists($directory) ? count($this->files($directory)) == 0 : false;
    }

    protected function getFinder($directory, $match = [], $ignore = [], $ignore_dotfiles = false): Finder
    {
        $finder = Finder::create()
            ->in($directory)
            ->ignoreDotFiles($ignore_dotfiles)
            ->notName('.DS_Store');

        collect($match)->each(function ($pattern) use ($finder): void {
            $finder->path($this->getWildcardRegex($pattern));
        });

        collect($ignore)->each(function ($pattern) use ($finder): void {
            $finder->notPath($this->getWildcardRegex($pattern));
        });

        return $finder;
    }

    protected function getWildcardRegex($pattern): string
    {
        return '#^' . str_replace('\*', '[^/]+', preg_quote(trim($pattern, '/'))) . '($|/)#';
    }
}
