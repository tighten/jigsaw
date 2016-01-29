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
        list($frontmatter, $content) = $this->parseFile($file);

        $bladeContent = $this->compileToBlade($frontmatter, $content);

        $data = array_merge($data, $frontmatter);

        return $this->temporaryFilesystem->put($bladeContent, function ($path) use ($data) {
            return $this->viewFactory->file($path, $data)->render();
        }, '.blade.php');
    }

    private function parseFile($file)
    {
        $document = $this->parser->parse($file->getContents());

        return [$document->getYAML(), $document->getContent()];
    }

    private function compileToBlade($frontmatter, $content)
    {
        return collect([
            sprintf("@extends('%s')", $frontmatter['extends']),
            sprintf("@section('%s')", $frontmatter['section']),
            $content,
            '@endsection',
        ])->implode("\n");
    }
}
