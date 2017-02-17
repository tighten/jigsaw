<?php namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\File\OutputFile;
use TightenCo\Jigsaw\PageData;
use TightenCo\Jigsaw\Parsers\FrontMatterParser;
use TightenCo\Jigsaw\View\ViewRenderer;

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

    public function handleCollectionItem($file, PageData $pageData)
    {
        return $this->buildOutput($file, $pageData);
    }

    public function handle($file, $pageData)
    {
        $pageData->page->addVariables($this->getPageVariables($file));

        return $this->buildOutput($file, $pageData);
    }

    private function getPageVariables($file)
    {
        return array_merge(['section' => 'content'], $this->parseFrontMatter($file));
    }

    public function buildOutput($file, PageData $pageData)
    {
        return collect($pageData->page->extends)->map(function ($layout) use ($file, $pageData) {
            $extension = $this->view->getExtension($layout);

            return new OutputFile(
                $file->getRelativePath(),
                $file->getFilenameWithoutExtension(),
                $extension == 'php' ? 'html' : $extension,
                $this->render($file->bladeViewPath(), $pageData, $layout),
                $pageData
            );
        });
    }

    private function render($includePath, $pageData, $layout)
    {
        return $this->temporaryFilesystem->put($this->compileToBlade($includePath, $pageData, $layout), function ($path) use ($pageData) {
            return $this->view->render($path, $pageData);
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
            sprintf("@section('%s')", $data->page->section),
            sprintf("@include('%s')", $includePath),
            '@endsection',
        ])->implode("\n");
    }
}
