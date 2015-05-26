<?php namespace Jigsaw\Jigsaw;

class ProcessedFile
{
    private $name;
    private $relativePath;
    private $contents;

    public function __construct($name, $relativePath, $contents)
    {
        $this->name = $name;
        $this->relativePath = $relativePath;
        $this->contents = $contents;
    }

    public function name()
    {
        return $this->name;
    }

    public function relativePath()
    {
        return $this->relativePath;
    }

    public function relativePathname()
    {
        return "{$this->relativePath}/{$this->name}";
    }

    public function contents()
    {
        return $this->contents;
    }
}
