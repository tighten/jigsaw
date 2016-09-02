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

    public function handleCollectionItem($file, ViewData $viewData)
    {
        return $this->buildOutput($file, $viewData);
    }

    public function handle($file, $data)
    {
        return $this->buildOutput(
            $file, new ViewData($data)
        );
    }

    public function buildOutput($file, ViewData $viewData)
    {
        $extension = strtolower($file->getExtension());

        return collect([
            new OutputFile(
                $file->getRelativePath(),
                $file->getFilenameWithoutExtension(),
                $extension == 'php' | $extension == 'html' ? 'html' : $extension,
                $this->render($file->getRealPath(), $viewData),
                $viewData
            )
        ]);
    }

    private function render($path, $data)
    {
        return $this->view->render($path, $data);
    }
}
