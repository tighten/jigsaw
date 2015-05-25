<?php namespace Jigsaw\Jigsaw;

use Illuminate\Contracts\View\Factory;

class BladeHandler
{
    private $viewFactory;

    public function __construct(Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    public function canHandle($file)
    {
        return ends_with($file->getFilename(), '.blade.php');
    }

    public function handle($file)
    {
        $path = $this->fullPath($file);
        $contents = $this->render($file);
        return new ProcessedFile($path, $contents);
    }

    public function render($file)
    {
        return $this->viewFactory->make($this->getViewName($file))->render();
    }

    public function fullPath($file)
    {
        return rtrim($file->getRelativePathname(), '.blade.php') . '.html';
    }

    private function getViewName($file)
    {
        return str_replace('/', '.', rtrim($file->getRelativePathname(), '.blade.php'));
    }
}
