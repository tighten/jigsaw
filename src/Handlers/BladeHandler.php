<?php namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\FrontMatterParser;
use TightenCo\Jigsaw\OutputFile;
use TightenCo\Jigsaw\ViewData;
use TightenCo\Jigsaw\ViewRenderer;

class BladeHandler
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
        return str_contains($file->getFilename(), '.blade.');
    }

    public function handleCollectionItem($file, ViewData $viewData)
    {
        return $this->buildOutput($file, $viewData);
    }

    public function handle($file, $data)
    {
        return $this->buildOutput(
            $file, new ViewData($data)
        );
    }

    public function buildOutput($file, ViewData $viewData)
    {
        $path = $file->getRelativePath();
        $filename = $file->getFilenameWithoutExtension();
        $extension = strtolower($file->getExtension());
        $fullPathName = $file->getRealPath();
        $renderedBladeWithFrontMatter = $this->parseFrontMatter($file, $viewData);

        return collect([
            new OutputFile(
                $path,
                $filename,
                $extension == 'php' | $extension == 'html' ? 'html' : $extension,
                $renderedBladeWithFrontMatter ?: $this->render($fullPathName, $viewData),
                $viewData
            )
        ]);
    }

    private function parseFrontMatter($file, $viewData)
    {
        $content = $file->getContents();
        $frontMatter = collect($this->parser->getFrontMatter($content));

        if (! $frontMatter->count()) {
            return;
        }

        $viewData = $this->addFrontMatterToViewData($frontMatter, $viewData);

        return $this->temporaryFilesystem->put(
            $this->parser->getBladeContent($content), function ($path) use ($viewData) {
                return $this->render($path, $viewData);
            },
        '.blade.php');
    }

    private function addFrontMatterToViewData($frontMatter, $viewData)
    {
        $frontMatter->each(function($value, $key) use ($viewData) {
            $viewData = $viewData->put($key, $value);
        });

        return $viewData;
    }

    private function render($path, $data)
    {
        return $this->view->render($path, $data);
    }
}
