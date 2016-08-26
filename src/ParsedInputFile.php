<?php namespace TightenCo\Jigsaw;

class ParsedInputFile extends InputFile
{
    protected $file;
    protected $basePath;

    public function __construct(InputFile $inputFile)
    {
        parent::__construct($inputFile->file, $inputFile->basePath);
    }

    public function hasBeenParsed()
    {
        return true;
    }
}
