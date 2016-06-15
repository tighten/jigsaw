<?php namespace TightenCo\Jigsaw;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;
use Symfony\Component\Finder\Finder;

class Filesystem extends BaseFilesystem
{
    public function allFiles($directory, $hidden = false)
    {
        return iterator_to_array(Finder::create()->ignoreDotFiles(false)->files()->in($directory), false);
    }
}
