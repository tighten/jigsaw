<?php namespace Jigsaw\Jigsaw\Handlers;

use Illuminate\Contracts\View\Factory;
use Jigsaw\Jigsaw\ProcessedFile;

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

    public function handle($file, $data)
    {
        $filename = $file->getBasename('.blade.php') . '.html';
        return new ProcessedFile($filename, $file->getRelativePath(), $this->render($file, $data));
    }

    public function render($file, $data)
    {
        return $this->viewFactory->make($this->getViewName($file), $data)->render();
    }

    private function getViewName($file)
    {
        return str_replace('/', '.', $file->getRelativePath()) . '.' . $file->getBasename('.blade.php');
    }
}
