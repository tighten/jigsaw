<?php

namespace TightenCo\Jigsaw\Handlers;

use Illuminate\Support\Str;
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
    private $paginator;

    private $parser;

    private $temporaryFilesystem;

    private $view;

    public function __construct(
        CollectionPaginator $paginator,
        FrontMatterParser $parser,
        TemporaryFilesystem $temporaryFilesystem,
        ViewRenderer $viewRenderer,
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

    public function handle($file, PageData $pageData): Collection
    {
        $page = $pageData->page;
        $page->addVariables($this->getPageVariables($file));
        $collection = $page->pagination->collection;
        $prefix = $page->pagination->prefix
            ?: $page->collections->{$collection}?->prefix
            ?: $page->prefix
            ?: '';
        $perPage = $page->pagination->perPage
            ?: $page->collections->{$collection}?->perPage
            ?: $page->perPage
            ?: 10;
        $extension = strtolower($file->getExtension());
        $outputExtension = ($extension == 'php' || $extension == 'md') ? 'html' : $extension;

        return $this->buildOutputFiles(
            $file->getRelativePath(),
            $file->getFilenameWithoutExtension(),
            $outputExtension,
            $pageData->get($collection),
            $perPage,
            $prefix,
            $pageData,
            fn ($pageData) => $this->renderFile($file, $pageData),
            $file,
        );
    }

    public function handleDefinition(string $relativePath, string $filename, string $template, $items, int $perPage, PageData $pageData): Collection
    {
        return $this->buildOutputFiles(
            $relativePath,
            $filename,
            'html',
            $items,
            $perPage,
            '',
            $pageData,
            fn ($pageData) => $this->view->renderView($template, $pageData),
        );
    }

    private function buildOutputFiles(string $relativePath, string $filename, string $extension, $items, int $perPage, string $prefix, PageData $pageData, callable $renderer, ?InputFile $inputFile = null): Collection
    {
        return $this->paginator->paginate(
            $relativePath,
            $filename,
            $items,
            $perPage,
            $prefix,
        )->map(function ($page) use ($relativePath, $filename, $extension, $pageData, $prefix, $renderer, $inputFile) {
            $pageData->setPagePath($page->current);
            $pageData->put('pagination', $page);

            return new OutputFile(
                $inputFile,
                $relativePath,
                $filename,
                $extension,
                $renderer($pageData),
                $pageData,
                $page->currentPage,
                $prefix,
            );
        });
    }

    private function getPageVariables($file)
    {
        return $this->parser->getFrontMatter($file->getContents());
    }

    private function renderFile($file, $pageData)
    {
        $bladeContent = $this->parser->getBladeContent($file->getContents());
        $bladeFilePath = $this->temporaryFilesystem->put(
            $bladeContent,
            $file->getPathname(),
            '.blade.php',
        );

        return $this->view->render($bladeFilePath, $pageData);
    }
}
