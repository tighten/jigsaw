<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\File;

use TightenCo\Jigsaw\PageData;

class CopyFile extends OutputFile
{
    /** @var string */
    protected $source;

    public function __construct(string $source, string $path, string $name, string $extension, PageData $data, int $page = 1)
    {
        $this->source = $source;
        parent::__construct($path, $name, $extension, null, $data, $page);
    }

    public function putContents($destination): bool
    {
        return copy($this->source, $destination);
    }
}
