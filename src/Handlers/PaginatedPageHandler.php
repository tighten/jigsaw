<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Handlers;

use Illuminate\Support\Collection;
use TightenCo\Jigsaw\Collection\CollectionPaginator;
use TightenCo\Jigsaw\File\InputFile;
use TightenCo\Jigsaw\File\OutputFile;
use TightenCo\Jigsaw\File\TemporaryFilesystem;
use TightenCo\Jigsaw\PageData;
use TightenCo\Jigsaw\Parsers\FrontMatterParser;
use TightenCo\Jigsaw\View\ViewRenderer;

class PaginatedPageHandler
{
    /** @var CollectionPaginator */
    private $paginator;

    /** @var FrontMatterParser */
    private $parser;

    /** @var TemporaryFilesystem */
    private $temporaryFilesystem;

    /** @var ViewRenderer */
    private $view;

    public function __construct(CollectionPaginator $paginator, FrontMatterParser $parser, TemporaryFilesystem $temporaryFilesystem, ViewRenderer $viewRenderer)
    {
        $this->paginator = $paginator;
        $this->parser = $parser;
        $this->temporaryFilesystem = $temporaryFilesystem;
        $this->view = $viewRenderer;
    }

    public function shouldHandle(InputFile $file): bool
    {
        if (! ends_with($file->getFilename(), '.blade.php')) {
            return false;
        }
        $content = $this->parser->parse($file->getContents());

        return isset($content->frontMatter['pagination']);
    }

    public function handle(InputFile $file, PageData $pageData): Collection
    {
        $pageData->page->addVariables($this->getPageVariables($file));

        return $this->paginator->paginate(
            $file,
            $pageData->get($pageData->page->pagination->collection),
            $pageData->page->pagination->perPage ?: ($pageData->page->perPage ?: 10)
        )->map(function ($page) use ($file, $pageData): OutputFile {
            $pageData->setPagePath($page->current);
            $pageData->put('pagination', $page);
            $extension = strtolower($file->getExtension());

            return new OutputFile(
                $file->getRelativePath(),
                $file->getFilenameWithoutExtension(),
                $extension == 'php' ? 'html' : $extension,
                $this->render($file, $pageData),
                $pageData,
                $page->currentPage
            );
        });
    }

    private function getPageVariables(InputFile $file): array
    {
        return $this->parser->getFrontMatter($file->getContents());
    }

    private function render(InputFile $file, PageData $pageData): string
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
