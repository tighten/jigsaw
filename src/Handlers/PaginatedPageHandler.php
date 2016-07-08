<?php namespace TightenCo\Jigsaw\Handlers;

use Illuminate\View\Factory;
use TightenCo\Jigsaw\OutputFile;
use TightenCo\Jigsaw\FrontMatterParser;
use TightenCo\Jigsaw\CollectionPaginator;

class PaginatedPageHandler
{
    private $viewFactory;

    public function __construct(Factory $viewFactory, FrontMatterParser $parser)
    {
        $this->viewFactory = $viewFactory;
        $this->parser = $parser;
    }

    public function shouldHandle($file)
    {
        list($frontMatter, $content) = $this->parser->parse($file->getContents());
        return isset($frontMatter['pagination']);
    }

    public function handle($file, $data)
    {
        list($frontMatter, $content) = $this->parser->parse($file->getContents());

        $items = $data['site'][$frontMatter['pagination']['for']];
        $perPage = array_get($frontMatter, 'pagination.perPage', 10);

        $paginator = new CollectionPaginator($items, $perPage);

        return $paginator->pages()->map(function ($page) use ($file, $data) {
            return new OutputFile(
                $file->getRelativePath(),
                $file->getBasename('.blade.php'),
                'html',
                $this->render($file, array_merge($data, ['pagination' => $page])), // <- need to pass paginated items here
                $data,
                $page['number']
            );
        })->all();
    }

    private function render($file, $data)
    {
        return $this->viewFactory->file($file->getRealPath(), $data)->render();
    }
}
