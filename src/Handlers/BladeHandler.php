<?php namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\OutputFile;
use TightenCo\Jigsaw\ViewData;
use TightenCo\Jigsaw\ViewRenderer;

class BladeHandler
{
    private $view;

    public function __construct(ViewRenderer $viewRenderer)
    {
        $this->view = $viewRenderer;
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
        return $this->view->render($file->getRealPath(), $data);
    }
}
