<?php namespace TightenCo\Jigsaw\Handlers;

use Illuminate\View\Factory;
use TightenCo\Jigsaw\FrontMatterParser;
use TightenCo\Jigsaw\OutputFile;
use TightenCo\Jigsaw\ViewData;

class PaginatedPageHandler
{
    private $paginator;
    private $viewFactory;
    private $parser;
    private $temporaryFilesystem;

    public function __construct($paginator, Factory $viewFactory, FrontMatterParser $parser, $temporaryFilesystem)
    {
        $this->paginator = $paginator;
        $this->viewFactory = $viewFactory;
        $this->parser = $parser;
        $this->temporaryFilesystem = $temporaryFilesystem;
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
                $file->getBasename('.blade.php'),
                'html',
                $this->render(
                    $content->content,
                    new ViewData($data->put('pagination', $page))
                ),
                $data,
                $page['page']
            );
        })->all();
    }

    private function render($bladeContent, $data)
    {
        return $this->temporaryFilesystem->put($bladeContent, function ($path) use ($data) {
            return $this->viewFactory->file($path, $data)->render();
        }, '.blade.php');
    }
}
