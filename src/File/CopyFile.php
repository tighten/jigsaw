<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\File;

class CopyFile extends OutputFile
{
    protected $source;

    public function __construct($source, $path, $name, $extension, $data, $page = 1)
    {
        $this->source = $source;
        parent::__construct($path, $name, $extension, null, $data, $page);
    }

    public function putContents($destination): bool
    {
        return copy($this->source, $destination);
    }
}
