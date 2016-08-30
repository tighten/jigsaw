<?php namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\FrontMatterParser;
use TightenCo\Jigsaw\OutputFile;
use TightenCo\Jigsaw\ViewData;
use TightenCo\Jigsaw\ViewRenderer;

class MarkdownHandler
{
    private $temporaryFilesystem;
    private $parser;
    private $view;

    public function __construct($temporaryFilesystem, FrontMatterParser $parser, ViewRenderer $viewRenderer)
    {
        $this->temporaryFilesystem = $temporaryFilesystem;
        $this->parser = $parser;
        $this->view = $viewRenderer;
    }

    public function shouldHandle($file)
    {
        return in_array($file->getExtension(), ['markdown', 'md']);
    }

    public function handle($file, $data)
    {
        if (! $file->hasBeenParsed()) {
            $document = $this->parseFile($file);
            $data = new ViewData($data
                ->put('section', 'content')
                ->merge($document->frontMatter)
                ->put('content', $document->content)
            );
        }

        return collect($data->extends)->map(function($layout) use ($file, $data) {

            return new OutputFile(
                $file->getRelativePath(),
                $file->getFilenameWithoutExtension(),
                'html',
                $this->render($data, $layout),
                $data
            );
        });
    }

    private function render($viewData, $layout)
    {
        return $this->temporaryFilesystem->put($this->compileToBlade($viewData, $layout), function ($path) use ($viewData) {
            return $this->view->render($path, $viewData);
        }, '.blade.php');
    }

    private function parseFile($file)
    {
        return $this->parser->parseMarkdown($file->getContents());
    }

    private function compileToBlade($data, $layout)
    {
        return collect([
            sprintf("@extends('%s')", $layout),
            sprintf("@section('%s')", $data->section),
            '{!! $jigsaw->content !!}',
            '@endsection',
        ])->implode("\n");
    }
}
