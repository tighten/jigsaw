<?php namespace TightenCo\Jigsaw\Handlers;

use Illuminate\View\Factory;
use TightenCo\Jigsaw\OutputFile;
use TightenCo\Jigsaw\FrontMatterParser;

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
        list($frontMatter, $content) = $this->parser->parse($file->getContents(), false);
        return isset($frontMatter['pagination']);
    }

    public function handle($file, $data)
    {
        return [
            new OutputFile(
                $file->getRelativePath(),
                $file->getBasename('.blade.php'),
                'html',
                $this->render($file, $data),
                $data
            )
        ];
    }

    private function render($file, $data)
    {
        return $this->viewFactory->file($file->getRealPath(), $data)->render();
    }
}
