<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\File;

use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class TemporaryFilesystem
{
    private $tempPath;
    private $filesystem;

    public function __construct($tempPath, $filesystem = null)
    {
        $this->tempPath = $tempPath;
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    public function buildTempPath($filename, $extension): string
    {
        return $this->tempPath . DIRECTORY_SEPARATOR .
            ($filename ? sha1($filename) : Str::random(32)) .
            $extension;
    }

    public function get($originalFilename, $extension): ?InputFile
    {
        $file = new SplFileInfo(
            $this->buildTempPath($originalFilename, $extension),
            $this->tempPath,
            $originalFilename . $extension
        );

        return $file->isReadable() ? new InputFile($file) : null;
    }

    public function put($contents, $filename, $extension): string
    {
        $path = $this->buildTempPath($filename, $extension);
        $this->filesystem->put($path, $contents);

        return $path;
    }

    public function hasTempDirectory(): bool
    {
        return $this->filesystem->exists($this->tempPath);
    }

    private function delete($path): void
    {
        $this->filesystem->delete($path);
    }
}
