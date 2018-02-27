<?php namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\File\OutputFile;
use TightenCo\Jigsaw\PageData;
use TightenCo\Jigsaw\Parsers\FrontMatterParser;
use TightenCo\Jigsaw\View\ViewRenderer;

class BladeHandler
{
    private $temporaryFilesystem;
    private $parser;
    private $view;
    private $hasFrontMatter;

    public function __construct($temporaryFilesystem, FrontMatterParser $parser, ViewRenderer $viewRenderer)
    {
        $this->temporaryFilesystem = $temporaryFilesystem;
        $this->parser = $parser;
        $this->view = $viewRenderer;
    }

    public function shouldHandle($file)
    {
        return str_contains($file->getFilename(), '.blade.');
    }

    public function handleCollectionItem($file, PageData $pageData)
    {
        $this->getPageVariables($file);

        return $this->buildOutput($file, $pageData);
    }

    public function handle($file, $pageData)
    {
        $pageData->page->addVariables($this->getPageVariables($file));

        return $this->buildOutput($file, $pageData);
    }

    private function buildOutput($file, $pageData)
    {
        $extension = strtolower($file->getExtension());

        return collect([
            new OutputFile(
                $file->getRelativePath(),
                $file->getFilenameWithoutExtension(),
                $extension == 'php' ? 'html' : $extension,
                $this->hasFrontMatter ?
                    $this->renderWithFrontMatter($file, $pageData) :
                    $this->render($file->getPathName(), $pageData),
                $pageData
            )
        ]);
    }

    private function getPageVariables($file)
    {
        $frontMatter = $this->parseFrontMatter($file);
        $this->hasFrontMatter = count($frontMatter) > 0;

        return $frontMatter;
    }

    private function parseFrontMatter($file)
    {
        return $this->parser->getFrontMatter($file->getContents());
    }

    private function render($path, $pageData)
    {
        return $this->view->render($path, $pageData);
    }

    private function renderWithFrontMatter($file, $pageData)
    {
        return $this->temporaryFilesystem->put(
            $this->parser->getBladeContent($file->getContents()),
            function ($path) use ($pageData) {
                return $this->render($path, $pageData);
            },
        '.blade.php'
        );
    }
}
