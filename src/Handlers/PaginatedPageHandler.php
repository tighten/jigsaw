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
        $content = $this->parser->parse($file->getContents());
        $items = $data[array_get($content->frontMatter, 'pagination.collection')];
        $perPage = array_get($content->frontMatter, 'pagination.perPage', 10);
        $pages = $this->paginator->paginate($file, $items, $perPage);

        return $pages->map(function ($page) use ($file, $data, $content) {
            return new OutputFile(
                $file->getRelativePath(),
                $file->getFilenameWithoutExtension(),
                'html',
                $this->render(
                    $content->content,
                    new ViewData(
                        $data->put('pagination', $page)
                        ->put('path', $page['pages'][$page['currentPage']])
                    )
                ),
                $data,
                $page['currentPage']
            );
        })->all();
    }

    private function render($bladeContent, $data)
    {
        return $this->temporaryFilesystem->put($bladeContent, function ($path) use ($data) {
            return $this->view->render($path, $data);
        }, '.blade.php');
    }
}
