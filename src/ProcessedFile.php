<?php namespace Jigsaw\Jigsaw;

class ProcessedFile
{
    private $relativePathname;
    private $contents;

    public function __construct($relativePathname, $contents)
    {
        $this->relativePathname = $relativePathname;
        $this->contents = $contents;
    }

    public function relativePathname()
    {
        return $this->relativePathname;
    }

    public function contents()
    {
        return $this->contents;
    }
}
