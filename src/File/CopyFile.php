<?php

namespace TightenCo\Jigsaw\File;

class CopyFile extends OutputFile
{
    protected $source;

    public function __construct(InputFile $file, $source, $path, $name, $extension, $data, $page = 1)
    {
        $this->source = $source;
        parent::__construct($file, $path, $name, $extension, null, $data, $page);
    }

    public function putContents($destination)
    {
        return copy($this->source, $destination);
    }
}
