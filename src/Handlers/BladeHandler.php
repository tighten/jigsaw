<?php namespace TightenCo\Jigsaw\Handlers;

use Illuminate\Contracts\View\Factory;
use TightenCo\Jigsaw\ProcessedFile;

class BladeHandler
{
    private $viewFactory;

    protected $extensions = [
        'php' => 'html',
    ];

    public function __construct(Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    public function canHandle($file)
    {
        return preg_match('@(.+).blade.(\w+)@', $file->getFilename());
    }

    public function handle($file, $data)
    {
        $filename = $this->getCompiledName($file);
        return new ProcessedFile($filename, $file->getRelativePath(), $this->render($file, $data));
    }

    private function getCompiledName($file)
    {
        preg_match('@(.+)\.blade\.(\w+)@', $file->getFilename(), $matches);
        return $matches[1] . '.' . $this->mutateExtension($matches[2]);
    }

    private function mutateExtension($extension)
    {
        if (isset($this->extensions[$extension])) {
            return $this->extensions[$extension];
        }

        return $extension;
    }

    public function render($file, $data)
    {
        return $this->viewFactory->file($file->getRealPath(), $data)->render();
    }
}
