<?php namespace TightenCo\Jigsaw\File;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;
use Symfony\Component\Finder\Finder;

class Filesystem extends BaseFilesystem
{
    public function getFile($directory, $filename)
    {
        return iterator_to_array(Finder::create()->files()->name($filename)->in($directory), false)[0];
    }

    public function allFiles($directory, $ignore_dotfiles = false)
    {
        return iterator_to_array(Finder::create()
            ->in($directory)
            ->ignoreDotFiles($ignore_dotfiles)
            ->notName('.DS_Store')
            ->files()
        , false);
    }
}
