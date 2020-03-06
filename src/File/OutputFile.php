<?php

namespace TightenCo\Jigsaw\File;

use TightenCo\Jigsaw\File\InputFile;
use TightenCo\Jigsaw\PageData;

class OutputFile
{
    private $inputFile;
    private $path;
    private $name;
    private $extension;
    private $contents;
    private $data;
    private $page;

    public function __construct(InputFile $inputFile, $path, $name, $extension, $contents, $data, $page = 1)
    {
        $this->setInputFile($inputFile, $data);
        $this->path = $path;
        $this->name = $name;
        $this->extension = $extension;
        $this->contents = $contents;
        $this->data = $data;
        $this->page = $page;
    }

    public function setInputFile(InputFile $inputFile, PageData $data)
    {
        $this->inputFile = $inputFile;
        $this->inputFile->setPageData($data);
    }

    public function inputFile()
    {
        return $this->inputFile;
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
