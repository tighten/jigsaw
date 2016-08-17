<?php namespace TightenCo\Jigsaw\Handlers;

use Mni\FrontYAML\Parser;
use TightenCo\Jigsaw\OutputFile;
use Illuminate\Contracts\View\Factory;

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

    public function shouldHandle($file)
    {
        return in_array($file->getExtension(), ['markdown', 'md']);
    }

    public function handle($file, $data)
    {
        $document = $this->parseFile($file);
        $data = array_merge($data, ['section' => 'markdown'], $document->getYAML());

        return [
            new OutputFile(
                $file->getRelativePath(),
                $file->getBasename('.'.$file->getExtension()),
                'html',
                $this->render($document, $data),
                $data
            )
        ];
    }

    private function render($document, $data)
    {
        $data = array_merge($data, [
            '__jigsawMarkdownContent' => $document->getContent()
        ]);

        $bladeContent = $this->compileToBlade($data['extends'], $data['section']);

        return $this->temporaryFilesystem->put($bladeContent, function ($path) use ($data) {
            return $this->viewFactory->file($path, $data)->render();
        }, '.blade.php');
    }

    private function parseFile($file)
    {
        return $this->parser->parse($file->getContents());
    }

    private function compileToBlade($extends, $section)
    {
        return collect([
            sprintf("@extends('%s')", $extends),
            sprintf("@section('%s')", $section),
            '{!! $__jigsawMarkdownContent !!}',
            '@endsection',
        ])->implode("\n");
    }
}
