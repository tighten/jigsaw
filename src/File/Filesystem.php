<?php

namespace TightenCo\Jigsaw\File;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Filesystem extends BaseFilesystem
{
    public function getFile($directory, $filename)
    {
        $filePath = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        return new SplFileInfo($filePath, $directory, $filename);
    }

    public function putWithDirectories($file_path, $contents)
    {
        $directory_path = collect(explode('/', $file_path));
        $directory_path->pop();
        $directory_path = trimPath($directory_path->implode('/'));

        if (! $this->isDirectory($directory_path)) {
            $this->makeDirectory($directory_path, 0755, true);
        }

        $this->put($file_path, $contents);
    }

    public function allFiles($directory, $ignore_dotfiles = false)
    {
        return iterator_to_array(
            Finder::create()
                ->in($directory)
                ->ignoreDotFiles($ignore_dotfiles)
                ->notName('.DS_Store')
                ->files(),
            false
        );
    }
}
