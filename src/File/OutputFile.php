<?php namespace TightenCo\Jigsaw\File;

class OutputFile
{
    private $path;
    private $name;
    private $extension;
    private $contents;
    private $data;
    private $page;

    public function __construct($path, $name, $extension, $contents, $data, $page = 1)
    {
        $this->path = $path;
        $this->name = $name;
        $this->extension = $extension;
        $this->contents = $contents;
        $this->data = $data;
        $this->page = $page;
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

    public function page()
    {
        return $this->page;
    }

    public function putContents($destination)
    {
        return file_put_contents($destination, $this->contents);
    }
}
