<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Handlers;

use Illuminate\Support\Collection;
use TightenCo\Jigsaw\Contracts\ItemHandler;
use TightenCo\Jigsaw\File\InputFile;
use TightenCo\Jigsaw\File\OutputFile;
use TightenCo\Jigsaw\File\TemporaryFilesystem;
use TightenCo\Jigsaw\PageData;
use TightenCo\Jigsaw\Parsers\FrontMatterParser;
use TightenCo\Jigsaw\View\ViewRenderer;

class MarkdownHandler implements ItemHandler
{
    /** @var TemporaryFilesystem */
    private $temporaryFilesystem;

    /** @var FrontMatterParser */
    private $parser;

    /** @var ViewRenderer */
    private $view;

    public function __construct(TemporaryFilesystem $temporaryFilesystem, FrontMatterParser $parser, ViewRenderer $viewRenderer)
    {
        $this->temporaryFilesystem = $temporaryFilesystem;
        $this->parser = $parser;
        $this->view = $viewRenderer;
    }

    public function shouldHandle(InputFile $file): bool
    {
        return in_array($file->getExtension(), ['markdown', 'md', 'mdown']);
    }

    public function handleCollectionItem(InputFile $file, PageData $pageData): Collection
    {
        return $this->buildOutput($file, $pageData);
    }

    public function handle(InputFile $file, PageData $pageData): Collection
    {
        $pageData->page->addVariables($this->getPageVariables($file));

        return $this->buildOutput($file, $pageData);
    }

    private function getPageVariables(InputFile $file): array
    {
        return array_merge(['section' => 'content'], $this->parseFrontMatter($file));
    }

    private function buildOutput(InputFile $file, PageData $pageData): Collection
    {
        return collect($pageData->page->extends)
            ->map(function ($extends, $templateToExtend) use ($file, $pageData): OutputFile {
                if ($templateToExtend) {
                    $pageData->setExtending($templateToExtend);
                }

                $extension = $this->view->getExtension($extends);

                return new OutputFile(
                    $file->getRelativePath(),
                    $file->getFileNameWithoutExtension(),
                    $extension == 'php' ? 'html' : $extension,
                    $this->render($file, $pageData, $extends),
                    $pageData
                );
            });
    }

    private function render(InputFile $file, PageData $pageData, string $extends): string
    {
        $uniqueFileName = $file->getPathname() . $extends;

        if ($cached = $this->getValidCachedFile($file, $uniqueFileName)) {
            return $this->view->render($cached->getPathname(), $pageData);
        } elseif ($file->isBladeFile()) {
            return $this->renderBladeMarkdownFile($file, $uniqueFileName, $pageData, $extends);
        } else {
            return $this->renderMarkdownFile($file, $uniqueFileName, $pageData, $extends);
        }
    }

    private function renderMarkdownFile(InputFile $file, string $uniqueFileName, PageData $pageData, string $extends): string
    {
        $html = $this->parser->parseMarkdownWithoutFrontMatter(
            $this->getEscapedMarkdownContent($file)
        );
        $wrapper = $this->view->renderString(
            "@extends('{$extends}')\n" .
            "@section('{$pageData->page->section}'){$html}@endsection"
        );

        return $this->view->render(
            $this->temporaryFilesystem->put($wrapper, $uniqueFileName, '.php'),
            $pageData
        );
    }

    private function renderBladeMarkdownFile(InputFile $file, string $uniqueFileName, PageData $pageData, string $extends): string
    {
        $contentPath = $this->renderMarkdownContent($file);

        return $this->view->render(
            $this->renderBladeWrapper(
                $uniqueFileName,
                basename($contentPath, '.blade.md'),
                $pageData,
                $extends
            ),
            $pageData
        );
    }

    private function renderMarkdownContent(InputFile $file): string
    {
        return $this->temporaryFilesystem->put(
            $this->getEscapedMarkdownContent($file),
            $file->getPathname(),
            '.blade.md'
        );
    }

    private function renderBladeWrapper(string $sourceFileName, string $contentFileName, PageData $pageData, string $extends): string
    {
        return $this->temporaryFilesystem->put(
            $this->makeBladeWrapper($contentFileName, $pageData, $extends),
            $sourceFileName,
            '.blade.php'
        );
    }

    private function makeBladeWrapper(string $path, PageData $pageData, string $extends): string
    {
        return collect([
            "@extends('{$extends}')",
            "@section('{$pageData->page->section}')",
            "@include('{$path}')",
            '@endsection',
        ])->implode("\n");
    }

    private function getValidCachedFile(InputFile $file, string $uniqueFileName): ?InputFile
    {
        $extension = $file->isBladeFile() ? '.blade.md' : '.php';
        $cached = $this->temporaryFilesystem->get($uniqueFileName, $extension);

        if ($cached && $cached->getLastModifiedTime() >= $file->getLastModifiedTime()) {
            return $cached;
        }

        return null;
    }

    private function getEscapedMarkdownContent(InputFile $file): string
    {
        $replacements = ['<?php' => "<{{'?php'}}"];

        if (in_array($file->getFullExtension(), ['markdown', 'md', 'mdown'])) {
            $replacements = array_merge([
                ' @' => " {{'@'}}",
                "\n@" => "\n{{'@'}}",
                '`@' => "`{{'@'}}",
                '{{' => '@{{',
                '{!!' => '@{!!',
            ], $replacements);
        }

        return strtr($file->getContents(), $replacements);
    }

    private function parseFrontMatter(InputFile $file): array
    {
        return $this->parser->getFrontMatter($file->getContents());
    }
}
