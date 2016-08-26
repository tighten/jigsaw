<?php namespace TightenCo\Jigsaw;

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
        $path = $this->tempPath . '/' . Str::quickRandom(32) . $extension;
        $this->filesystem->put($path, $contents);
        $result = $callback($path);
        $this->filesystem->delete($path);

        return $result;
    }
}
