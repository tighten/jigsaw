<?php

namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\File\OutputFile;
use TightenCo\Jigsaw\File\TemporaryFilesystem;
use TightenCo\Jigsaw\PageData;
use TightenCo\Jigsaw\Parsers\FrontMatterParser;
use TightenCo\Jigsaw\View\ViewRenderer;

class MarkdownHandler
{
    private $temporaryFilesystem;
    private $parser;
    private $view;

    public function __construct(TemporaryFilesystem $temporaryFilesystem, FrontMatterParser $parser, ViewRenderer $viewRenderer)
    {
        $this->temporaryFilesystem = $temporaryFilesystem;
        $this->parser = $parser;
        $this->view = $viewRenderer;
    }

    public function shouldHandle($file)
    {
        return in_array($file->getExtension(), ['markdown', 'md', 'mdown']);
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

    private function buildOutput($file, PageData $pageData)
    {
        return collect($pageData->page->extends)
            ->map(function ($extends, $templateToExtend) use ($file, $pageData) {
                if ($templateToExtend) {
                    $pageData->setExtending($templateToExtend);
                }

                $extension = $this->view->getExtension($extends);

                return new OutputFile(
                    $file,
                    $file->getRelativePath(),
                    $file->getFileNameWithoutExtension(),
                    $extension == 'php' ? 'html' : $extension,
                    $this->render($file, $pageData, $extends),
                    $pageData
                );
            });
    }

    private function render($file, $pageData, $extends)
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

    private function renderMarkdownFile($file, $uniqueFileName, $pageData, $extends)
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

    private function renderBladeMarkdownFile($file, $uniqueFileName, $pageData, $extends)
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

    private function renderMarkdownContent($file)
    {
        return $this->temporaryFilesystem->put(
            $this->getEscapedMarkdownContent($file),
            $file->getPathname(),
            '.blade.md'
        );
    }

    private function renderBladeWrapper($sourceFileName, $contentFileName, $pageData, $extends)
    {
        return $this->temporaryFilesystem->put(
            $this->makeBladeWrapper($contentFileName, $pageData, $extends),
            $sourceFileName,
            '.blade.php'
        );
    }

    private function makeBladeWrapper($path, $pageData, $extends)
    {
        return collect([
            "@extends('{$extends}')",
            "@section('{$pageData->page->section}')",
            "@include('{$path}')",
            '@endsection',
        ])->implode("\n");
    }

    private function getValidCachedFile($file, $uniqueFileName)
    {
        $extension = $file->isBladeFile() ? '.blade.md' : '.php';
        $cached = $this->temporaryFilesystem->get($uniqueFileName, $extension);

        if ($cached && $cached->getLastModifiedTime() >= $file->getLastModifiedTime()) {
            return $cached;
        }
    }

    private function getEscapedMarkdownContent($file)
    {
        $replacements = ['<?php' => "<{{'?php'}}"];

        if (in_array($file->getFullExtension(), ['markdown', 'md', 'mdown'])) {
            $replacements = array_merge([
                '@' => "{{'@'}}",
                '{{' => '@{{',
                '{!!' => '@{!!',
            ], $replacements);
        }

        return strtr($file->getContents(), $replacements);
    }

    private function parseFrontMatter($file)
    {
        return $this->parser->getFrontMatter($file->getContents());
    }
}
