<?php namespace TightenCo\Jigsaw\Handlers;

use Illuminate\View\Factory;
use TightenCo\Jigsaw\OutputFile;
use TightenCo\Jigsaw\ViewData;

class BladeHandler
{
    private $viewFactory;

    public function __construct(Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    public function shouldHandle($file)
    {
        return ends_with($file->getFilename(), '.blade.php');
    }

    public function handle($file, $data)
    {
        return [
            new OutputFile(
                $file->getRelativePath(),
                $file->getBasename('.blade.php'),
                'html',
                $this->render($file, new ViewData($data)),
                $data
            )
        ];
    }

    private function render($file, $data)
    {
        return $this->viewFactory->file($file->getRealPath(), $data)->render();
    }
}
