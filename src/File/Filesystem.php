<?php namespace TightenCo\Jigsaw\File;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;
use Symfony\Component\Finder\Finder;

class Filesystem extends BaseFilesystem
{
    public function getFile($directory, $filename)
    {
        return iterator_to_array(Finder::create()->files()->name($filename)->in($directory), false)[0];
    }

    public function allFiles($directory, $hidden = false)
    {
        return iterator_to_array(Finder::create()->ignoreDotFiles($hidden)->files()->in($directory), false);
    }
}
