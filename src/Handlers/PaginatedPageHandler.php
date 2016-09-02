<?php namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\FrontMatterParser;
use TightenCo\Jigsaw\OutputFile;
use TightenCo\Jigsaw\ViewData;
use TightenCo\Jigsaw\ViewRenderer;

class PaginatedPageHandler
{
    private $paginator;
    private $parser;
    private $temporaryFilesystem;
    private $viewFactory;

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

    public function handle($file, $data)
    {
        $fileContent = $file->getContents();
        $bladeContent = $this->parser->getBladeContent($fileContent);
        $viewData = $this->addFrontMatterToViewData($fileContent, new ViewData($data));

        $collection = $viewData->pagination->collection;
        $perPage = $viewData->pagination->perPage ?: 10;
        $pages = $this->paginator->paginate($file, $viewData->get($collection), $perPage);

        return $pages->map(function ($page) use ($file, $viewData, $bladeContent) {
            $extension = strtolower($file->getExtension());
            $currentPage = $page['currentPage'];

            return new OutputFile(
                $file->getRelativePath(),
                $file->getFilenameWithoutExtension(),
                $extension == 'php' | $extension == 'html' ? 'html' : $extension,
                $this->render(
                    $bladeContent,
                    $viewData->put('pagination', $page)->put('path', $page['pages'][$currentPage])
                ),
                $viewData,
                $currentPage
            );
        })->all();
    }

    private function addFrontMatterToViewData($fileContent, $viewData)
    {
        collect($this->parser->getFrontMatter($fileContent))->each(function($value, $key) use ($viewData) {
            $viewData = $viewData->put($key, $value);
        });

        return $viewData;
    }

    private function render($bladeContent, $data)
    {
        return $this->temporaryFilesystem->put($bladeContent, function ($path) use ($data) {
            return $this->view->render($path, $data);
        }, '.blade.php');
    }
}
