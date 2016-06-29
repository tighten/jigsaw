<?php namespace TightenCo\Jigsaw;

class ProcessedFile
{
    private $name;
    private $relativePath;
    private $contents;
    private $data;

    public function __construct($name, $relativePath, $contents, $data)
    {
        $this->name = $name;
        $this->relativePath = $relativePath;
        $this->contents = $contents;
        $this->data = $data;
    }

    public function name()
    {
        return $this->name;
    }

    public function data()
    {
        return $this->data;
    }

    public function basename()
    {
        return pathinfo($this->name, PATHINFO_FILENAME);
    }

    public function extension()
    {
        return pathinfo($this->name, PATHINFO_EXTENSION);
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
