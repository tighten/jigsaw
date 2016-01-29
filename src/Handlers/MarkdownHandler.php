<?php namespace Jigsaw\Jigsaw\Handlers;

use Illuminate\Contracts\View\Factory;
use Jigsaw\Jigsaw\ProcessedFile;
use Mni\FrontYAML\Parser;

class MarkdownHandler
{
    private $temporaryFilesystem;
    private $viewFactory;
    private $parser;

    public function __construct($temporaryFilesystem, Factory $viewFactory, $parser = null)
    {
        $this->temporaryFilesystem = $temporaryFilesystem;
        $this->viewFactory = $viewFactory;
        $this->parser = $parser ?: new Parser;
    }

    public function canHandle($file)
    {
        return in_array($file->getExtension(), ['markdown', 'md']);
    }

    public function handle($file, $data)
    {
        $filename = $file->getBasename($this->getFileExtension($file)) . '.html';
        return new ProcessedFile($filename, $file->getRelativePath(), $this->render($file, $data));
    }

    private function getFileExtension($file)
    {
        return '.' . $file->getExtension();
    }

    public function render($file, $data)
    {
        return $this->temporaryFilesystem->put($this->compileToBlade($file), function ($path) use ($data) {
            return $this->viewFactory->file($path, $data)->render();
        }, '.blade.php');
    }

    private function compileToBlade($file)
    {
        $document = $this->parser->parse($file->getContents());
        $yaml = $document->getYAML();

        return collect([
            sprintf("@extends('%s')", $yaml['extends']),
            sprintf("@section('%s')", $yaml['section']),
            $document->getContent(),
            '@endsection',
        ])->implode("\n");
    }
}
