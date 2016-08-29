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
            $data = new ViewData(
                $data->put('section', 'content')
                ->merge($document->frontMatter)
                ->put('content', $document->content)
            );
        }

        if (! $data->extends) {
            return;
        }

        return [
            new OutputFile(
                $file->getRelativePath(),
                $file->getFilenameWithoutExtension(),
                'html',
                $this->render($data),
                $data
            )
        ];
    }

    private function render($viewData)
    {
        return $this->temporaryFilesystem->put($this->compileToBlade($viewData), function ($path) use ($viewData) {
            return $this->view->render($path, $viewData);
        }, '.blade.php');
    }

    private function parseFile($file)
    {
        return $this->parser->parse($file->getContents());
    }

    private function compileToBlade($data)
    {
        return collect([
            sprintf("@extends('%s')", $data->extends),
            sprintf("@section('%s')", $data->section),
            '{!! $jigsaw->content !!}',
            '@endsection',
        ])->implode("\n");
    }
}
