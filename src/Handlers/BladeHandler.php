<?php namespace TightenCo\Jigsaw\Handlers;

use Illuminate\Contracts\View\Factory;
use TightenCo\Jigsaw\ProcessedFile;

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
        return new ProcessedFile($filename, $file->getRelativePath(), $this->render($file, $data), $data);
    }

    private function render($file, $data)
    {
        return $this->viewFactory->file($file->getRealPath(), $data)->render();
    }
}
