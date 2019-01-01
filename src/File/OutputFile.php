<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\File;

use TightenCo\Jigsaw\PageData;

class OutputFile
{
    /** @var string */
    private $path;

    /** @var string */
    private $name;

    /** @var string */
    private $extension;

    /** @var string */
    private $contents;

    /** @var PageData */
    private $data;

    /** @var int */
    private $page;

    public function __construct(string $path, string $name, string $extension, string $contents, PageData $data, int $page = 1)
    {
        $this->path = $path;
        $this->name = $name;
        $this->extension = $extension;
        $this->contents = $contents;
        $this->data = $data;
        $this->page = $page;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function extension(): string
    {
        return $this->extension;
    }

    public function contents(): string
    {
        return $this->contents;
    }

    public function data(): PageData
    {
        return $this->data;
    }

    public function page(): int
    {
        return $this->page;
    }

    public function putContents(string $destination): bool
    {
        return file_put_contents($destination, $this->contents) !== false;
    }
}
