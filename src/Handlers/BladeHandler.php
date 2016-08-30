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
        if (strtolower($file->getExtension()) === 'php') {
            $extension = 'html';
            $sourceExtension = '.blade.php';
            $contents = $this->render($file->getRealPath(), new ViewData($data));
        } else {
            $extension = $file->getExtension();
            $sourceExtension = '.blade.' . $extension;
            $contents = $this->renderCopy($file->getRealPath(), new ViewData($data));
        }

        return [
            new OutputFile(
                $file->getRelativePath(),
                $file->getBasename($sourceExtension),
                $extension,
                $contents,
                $data
            )
        ];
    }

    private function renderCopy($path, $data)
    {
        return $this->temporaryFilesystem->copy($path, function ($copy) use ($data) {
            return $this->render($copy, $data);
        }, '.blade.php');
    }

    private function render($path, $data)
    {
        return $this->view->render($path, $data);
    }
}
