<?php namespace TightenCo\Jigsaw;

class OutputFile
{
    private $path;
    private $name;
    private $extension;
    private $contents;
    private $data;

    public function __construct($path, $name, $extension, $contents, $data)
    {
        $this->path = $path;
        $this->name = $name;
        $this->extension = $extension;
        $this->contents = $contents;
        $this->data = $data;
    }

    public function path()
    {
        return $this->path;
    }

    public function name()
    {
        return $this->name;
    }

    public function extension()
    {
        return $this->extension;
    }

    public function contents()
    {
        return $this->contents;
    }

    public function data()
    {
        return $this->data;
    }
}
