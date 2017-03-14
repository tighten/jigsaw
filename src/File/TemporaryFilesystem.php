<?php namespace TightenCo\Jigsaw\File;

use Illuminate\Support\Str;

class TemporaryFilesystem
{
    private $tempPath;
    private $filesystem;

    public function __construct($tempPath, $filesystem = null)
    {
        $this->tempPath = $tempPath;
        $this->filesystem = $filesystem ?: new Filesystem;
    }

    public function put($contents, $callback, $extension = '')
    {
        $path = $this->buildTempPath($extension);
        $this->filesystem->put($path, $contents);

        return $this->cleanup($path, $callback);
    }

    private function buildTempPath($extension)
    {
        return $this->tempPath . '/' . Str::random(32) . $extension;
    }

    private function cleanup($path, $callback)
    {
        $result = $callback($path);
        $this->filesystem->delete($path);

        return $result;
    }
}
