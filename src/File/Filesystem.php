<?php namespace TightenCo\Jigsaw\File;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;
use Symfony\Component\Finder\Finder;

class Filesystem extends BaseFilesystem
{
    public function getFile($directory, $filename)
    {
        return iterator_to_array(Finder::create()->files()->name($filename)->in($directory), false)[0];
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

    public function allFiles($directory, $ignore_dotfiles = false, $ignore = [])
    {
        return iterator_to_array(
            $this->getFinder($directory, $ignore_dotfiles, $ignore)->files(),
            false
        );
    }

    public function allDirectories($directory, $ignore_dotfiles = false, $ignore = [])
    {
        return iterator_to_array(
            $this->getFinder($directory, $ignore_dotfiles, $ignore)->directories(),
            false
        );
    }

    public function allFilesAndDirectories($directory, $ignore_dotfiles = false, $ignore = [])
    {
        return iterator_to_array(
            $this->getFinder($directory, $ignore_dotfiles, $ignore),
            false
        );
    }

    protected function getFinder($directory, $ignore_dotfiles = false, $ignore = [])
    {
        $finder = Finder::create()
            ->in($directory)
            ->ignoreDotFiles($ignore_dotfiles)
            ->notName('.DS_Store');

        collect($ignore)->each(function ($pattern) use ($finder) {
            $finder->notPath('#^' . str_replace('\*', '[^/]+', preg_quote(trim($pattern, '/'))) . '#');
        });

        return $finder;
    }
}
