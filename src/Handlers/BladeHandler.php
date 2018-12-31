<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Handlers;

use Illuminate\Support\Collection;
use TightenCo\Jigsaw\File\OutputFile;
use TightenCo\Jigsaw\File\TemporaryFilesystem;
use TightenCo\Jigsaw\PageData;
use TightenCo\Jigsaw\Parsers\FrontMatterParser;
use TightenCo\Jigsaw\View\ViewRenderer;

class BladeHandler
{
    /** @var TemporaryFilesystem */
    private $temporaryFilesystem;

    /** @var FrontMatterParser */
    private $parser;

    /** @var ViewRenderer */
    private $view;

    /** @var bool */
    private $hasFrontMatter;

    public function __construct(TemporaryFilesystem $temporaryFilesystem, FrontMatterParser $parser, ViewRenderer $viewRenderer)
    {
        $this->temporaryFilesystem = $temporaryFilesystem;
        $this->parser = $parser;
        $this->view = $viewRenderer;
    }

    public function shouldHandle($file): bool
    {
        return str_contains($file->getFilename(), '.blade.');
    }

    public function handleCollectionItem($file, PageData $pageData): Collection
    {
        $this->getPageVariables($file);

        return $this->buildOutput($file, $pageData);
    }

    public function handle($file, $pageData): Collection
    {
        $pageData->page->addVariables($this->getPageVariables($file));

        return $this->buildOutput($file, $pageData);
    }

    private function buildOutput($file, $pageData): Collection
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
            ),
        ]);
    }

    private function getPageVariables($file): array
    {
        $frontMatter = $this->parseFrontMatter($file);
        $this->hasFrontMatter = count($frontMatter) > 0;

        return $frontMatter;
    }

    private function parseFrontMatter($file): array
    {
        return $this->parser->getFrontMatter($file->getContents());
    }

    private function render($path, $pageData): string
    {
        return $this->view->render($path, $pageData);
    }

    private function renderWithFrontMatter($file, $pageData): string
    {
        $bladeFilePath = $this->temporaryFilesystem->put(
            $this->parser->getBladeContent($file->getContents()),
            $file->getPathname(),
            '.blade.php'
        );

        return $this->render($bladeFilePath, $pageData);
    }
}
