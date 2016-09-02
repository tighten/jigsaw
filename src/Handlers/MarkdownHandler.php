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

    public function handleCollectionItem($file, ViewData $viewData)
    {
        return $this->buildOutput($file, $viewData);
    }

    public function handle($file, $data)
    {
        return $this->buildOutput(
            $file,
            new ViewData($data->put('section', 'content')->merge($this->parseFrontMatter($file))
        ));
    }

    public function buildOutput($file, ViewData $viewData)
    {
        return collect($viewData->extends)->map(function($layout) use ($file, $viewData) {
            $extension = $this->view->getExtension($layout);

            return new OutputFile(
                $file->getRelativePath(),
                $file->getFilenameWithoutExtension(),
                $extension == 'php' | $extension == 'html' ? 'html' : $extension,
                $this->render($file->bladeViewPath(), $viewData, $layout),
                $viewData
            );
        });
    }

    private function render($includePath, $viewData, $layout)
    {
        return $this->temporaryFilesystem->put($this->compileToBlade($includePath, $viewData, $layout), function ($path) use ($viewData) {
            return $this->view->render($path, $viewData);
        }, '.blade.php');
    }

    private function parseFrontMatter($file)
    {
        return $this->parser->getFrontMatter($file->getContents());
    }

    private function compileToBlade($includePath, $data, $layout)
    {
        return collect([
            sprintf("@extends('%s')", $layout),
            sprintf("@section('%s')", $data->section),
            sprintf("@include('%s')", $includePath),
            '@endsection',
        ])->implode("\n");
    }
}
