<?php

namespace TightenCo\Jigsaw\View;

use Illuminate\Support\Collection;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;

class ViewRenderer
{
    private $viewFactory;
    private $bladeCompiler;
    private $extensionEngines = [
        'md' => 'markdown',
        'markdown' => 'markdown',
        'mdown' => 'markdown',
        'blade.md' => 'blade-markdown',
        'blade.mdown' => 'blade-markdown',
        'blade.markdown' => 'blade-markdown',
    ];
    private $bladeExtensions = [
        'js', 'json', 'xml', 'yaml', 'yml', 'rss', 'atom', 'txt', 'text', 'html',
    ];

    public function __construct(Factory $viewFactory, BladeCompiler $bladeCompiler, Collection $config = null)
    {
        $this->config = $config ?? collect();
        $this->viewFactory = $viewFactory;
        $this->bladeCompiler = $bladeCompiler;
        $this->finder = $this->viewFactory->getFinder();
        $this->addExtensions();
        $this->addHintpaths();
    }

    public function getExtension($bladeViewPath)
    {
        return strtolower(pathinfo($this->finder->find($bladeViewPath), PATHINFO_EXTENSION));
    }

    public function render($path, $data)
    {
        return $this->viewFactory->file($path, $data->all())->render();
    }

    public function renderString($string)
    {
        return $this->bladeCompiler->compileString($string);
    }

    private function addHintpaths()
    {
        collect($this->config->get('viewHintPaths'))->each(function ($path, $hint) {
            $this->addHintpath($hint, $path);
        });
    }

    private function addHintPath($hint, $path)
    {
        $this->viewFactory->addNamespace($hint, $path);
    }

    private function addExtensions()
    {
        collect($this->extensionEngines)->each(function ($engine, $extension) {
            $this->viewFactory->addExtension($extension, $engine);
        });

        collect($this->bladeExtensions)->each(function ($extension) {
            $this->viewFactory->addExtension($extension, 'php');
            $this->viewFactory->addExtension('blade.' . $extension, 'blade');
        });
    }
}
