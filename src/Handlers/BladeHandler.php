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
        return new ProcessedFile('index.html', $this->prettyPath($file), $this->render($file, $data));
    }

    public function render($file, $data)
    {
        return $this->viewFactory->make($this->getViewName($file), $data)->render();
    }

    private function prettyPath($file)
    {
        $basename = $file->getBasename('.blade.php');

        if ($basename !== 'index') {
            return $file->getRelativePath() . '/' . $basename;
        }

        return $file->getRelativePath();
    }

    private function getViewName($file)
    {
        return str_replace('/', '.', $file->getRelativePath()) . '.' . $file->getBasename('.blade.php');
    }
}
