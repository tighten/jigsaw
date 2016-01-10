<?php namespace Jigsaw\Jigsaw\Handlers;

use Illuminate\Contracts\View\Factory;
use Jigsaw\Jigsaw\ProcessedFile;
use Mni\FrontYAML\Parser;

class MarkdownHandler
{
    private $tempPath;
    private $viewFactory;
    private $parser;

    public function __construct($tempPath, Factory $viewFactory, $parser = null)
    {
        $this->tempPath = $tempPath;
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
        $str = $file->getContents();
        $document = $this->parser->parse($str);
        $yaml = $document->getYAML();

        $bladeContents = collect([
            sprintf("@extends('%s')", $yaml['layout']),
            sprintf("@section('%s')", $yaml['section']),
            $document->getContent(),
            '@endsection',
        ])->implode("\n");

        $path = $this->tempPath . '/' . sha1($file->getRealPath()) . '.blade.php';
        file_put_contents($path, $bladeContents);

        $renderedContents = $this->viewFactory->file($path, $data)->render();

        unlink($path);

        return $renderedContents;
    }
}
