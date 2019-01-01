<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\File;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Filesystem extends BaseFilesystem
{
    public function getFile(string $directory, string $filename): SplFileInfo
    {
        $filePath = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        return new SplFileInfo($filePath, $directory, $filename);
    }

    public function putWithDirectories(string $file_path, string $contents): void
    {
        $directory_path = collect(explode('/', $file_path));
        $directory_path->pop();
        $directory_path = trimPath($directory_path->implode('/'));

        if (! $this->isDirectory($directory_path)) {
            $this->makeDirectory($directory_path, 0755, true);
        }

        $this->put($file_path, $contents);
    }

    public function files($directory, $match = [], array $ignore = [], bool $ignore_dotfiles = false): array
    {
        return $directory ? iterator_to_array(
            $this->getFinder($directory, $match, $ignore, $ignore_dotfiles)->files(),
            false
        ) : [];
    }

    public function directories($directory, $match = [], array $ignore = [], bool $ignore_dotfiles = false): array
    {
        return $directory ? iterator_to_array(
            $this->getFinder($directory, $match, $ignore, $ignore_dotfiles)->directories(),
            false
        ) : [];
    }

    public function filesAndDirectories(string $directory, array $match = [], array $ignore = [], bool $ignore_dotfiles = false): array
    {
        return $directory ? iterator_to_array(
            $this->getFinder($directory, $match, $ignore, $ignore_dotfiles),
            false
        ) : [];
    }

    public function isEmptyDirectory(string $directory): bool
    {
        return $this->exists($directory) ? count($this->files($directory)) == 0 : false;
    }

    protected function getFinder(string $directory, array $match = [], array $ignore = [], bool $ignore_dotfiles = false): Finder
    {
        $finder = Finder::create()
            ->in($directory)
            ->ignoreDotFiles($ignore_dotfiles)
            ->notName('.DS_Store');

        collect($match)->each(function (string $pattern) use ($finder): void {
            $finder->path($this->getWildcardRegex($pattern));
        });

        collect($ignore)->each(function (string $pattern) use ($finder): void {
            $finder->notPath($this->getWildcardRegex($pattern));
        });

        return $finder;
    }

    protected function getWildcardRegex(string $pattern): string
    {
        return '#^' . str_replace('\*', '[^/]+', preg_quote(trim($pattern, '/'))) . '($|/)#';
    }
}
