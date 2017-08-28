<?php namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\File\OutputFile;
use TightenCo\Jigsaw\PageData;
use TightenCo\Jigsaw\Parsers\FrontMatterParser;
use TightenCo\Jigsaw\View\ViewRenderer;

class PaginatedPageHandler
{
    private $paginator;
    private $parser;
    private $temporaryFilesystem;
    private $view;

    public function __construct($paginator, FrontMatterParser $parser, $temporaryFilesystem, ViewRenderer $viewRenderer)
    {
        $this->paginator = $paginator;
        $this->parser = $parser;
        $this->temporaryFilesystem = $temporaryFilesystem;
        $this->view = $viewRenderer;
    }

    public function shouldHandle($file)
    {
        if (! ends_with($file->getFilename(), '.blade.php')) {
            return false;
        }
        $content = $this->parser->parse($file->getContents());

        return isset($content->frontMatter['pagination']);
    }

    public function handle($file, PageData $pageData)
    {
        $pageData->page->addVariables($this->getPageVariables($file));

        return $this->paginator->paginate(
            $file,
            $pageData->get($pageData->page->pagination->collection),
            $pageData->page->pagination->perPage ?: ($pageData->page->perPage ?: 10)
        )->map(function ($page) use ($file, $pageData) {
            $pageData->setPagePath($page->current);
            $pageData->put('pagination', $page);
            $extension = strtolower($file->getExtension());

            return new OutputFile(
                $file->getRelativePath(),
                $file->getFilenameWithoutExtension(),
                $extension == 'php' ? 'html' : $extension,
                $this->render(
                    $this->parser->getBladeContent($file->getContents()),
                    $pageData
                ),
                $pageData,
                $page->currentPage
            );
        });
    }

    private function getPageVariables($file)
    {
        return $this->parser->getFrontMatter($file->getContents());
    }

    private function render($bladeContent, $pageData)
    {
        return $this->temporaryFilesystem->put($bladeContent, function ($path) use ($pageData) {
            return $this->view->render($path, $pageData);
        }, '.blade.php');
    }
}
