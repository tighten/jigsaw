<?php

namespace TightenCo\Jigsaw\Handlers;

use Illuminate\Support\Str;
use TightenCo\Jigsaw\Collection\CollectionPaginator;
use TightenCo\Jigsaw\File\OutputFile;
use TightenCo\Jigsaw\File\TemporaryFilesystem;
use TightenCo\Jigsaw\PageData;
use TightenCo\Jigsaw\Parsers\FrontMatterParser;
use TightenCo\Jigsaw\View\ViewRenderer;

class PaginatedPageHandler
{
    private $paginator;
    private $parser;
    private $temporaryFilesystem;
    private $view;

    public function __construct(
        CollectionPaginator $paginator,
        FrontMatterParser $parser,
        TemporaryFilesystem $temporaryFilesystem,
        ViewRenderer $viewRenderer
    ) {
        $this->paginator = $paginator;
        $this->parser = $parser;
        $this->temporaryFilesystem = $temporaryFilesystem;
        $this->view = $viewRenderer;
    }

    public function shouldHandle($file)
    {
        if (! Str::endsWith($file->getFilename(), ['.blade.md', '.blade.php'])) {
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
                $file,
                $file->getRelativePath(),
                $file->getFilenameWithoutExtension(),
                ($extension == 'php' || $extension == 'md') ? 'html' : $extension,
                $this->render($file, $pageData),
                $pageData,
                $page->currentPage
            );
        });
    }

    private function getPageVariables($file)
    {
        return $this->parser->getFrontMatter($file->getContents());
    }

    private function render($file, $pageData)
    {
        $bladeContent = $this->parser->getBladeContent($file->getContents());
        $bladeFilePath = $this->temporaryFilesystem->put(
            $bladeContent,
            $file->getPathname(),
            '.blade.php'
        );

        return $this->view->render($bladeFilePath, $pageData);
    }
}
