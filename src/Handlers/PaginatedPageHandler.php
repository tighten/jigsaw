<?php namespace TightenCo\Jigsaw\Handlers;

use Illuminate\View\Factory;
use TightenCo\Jigsaw\OutputFile;
use TightenCo\Jigsaw\FrontMatterParser;
use TightenCo\Jigsaw\CollectionPaginator;

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

        list($frontMatter, $content) = $this->parser->parse($file->getContents());
        return isset($frontMatter['pagination']);
    }

    public function handle($file, $data)
    {
        list($frontMatter, $content) = $this->parser->parse($file->getContents());

        $items = $data['site'][$frontMatter['pagination']['for']];
        $perPage = array_get($frontMatter, 'pagination.perPage', 10);

        $pages = $this->paginator->paginate($file, $items, $perPage);

        return $pages->map(function ($page) use ($file, $data, $content) {
            return new OutputFile(
                $file->getRelativePath(),
                $file->getBasename('.blade.php'),
                'html',
                $this->render($content, array_merge($data, ['pagination' => $page])),
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
