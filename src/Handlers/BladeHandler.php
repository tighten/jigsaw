<?php namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\OutputFile;
use TightenCo\Jigsaw\ViewData;
use TightenCo\Jigsaw\ViewRenderer;

class BladeHandler
{
    private $temporaryFilesystem;
    private $view;

    public function __construct($temporaryFilesystem, ViewRenderer $viewRenderer)
    {
        $this->temporaryFilesystem = $temporaryFilesystem;
        $this->view = $viewRenderer;
    }

    public function shouldHandle($file)
    {
        return str_contains($file->getFilename(), '.blade.');
    }

    public function handle($file, $data)
    {
        $extension = strtolower($file->getExtension());

        return [
            new OutputFile(
                $file->getRelativePath(),
                $file->getFilenameWithoutExtension(),
                $extension == 'php' | $extension == 'html' ? 'html' : $extension,
                $this->render($file->getRealPath(), new ViewData($data)),
                $data
            )
        ];
    }

    private function render($path, $data)
    {
        return $this->view->render($path, $data);
    }
}
