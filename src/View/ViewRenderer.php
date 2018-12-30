<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\View;

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
        'js', 'json', 'xml', 'rss', 'atom', 'txt', 'text', 'html',
    ];

    public function __construct(Factory $viewFactory, BladeCompiler $bladeCompiler)
    {
        $this->viewFactory = $viewFactory;
        $this->bladeCompiler = $bladeCompiler;
        $this->finder = $this->viewFactory->getFinder();
        $this->addExtensions();
    }

    public function getExtension($bladeViewPath): string
    {
        return strtolower(pathinfo($this->finder->find($bladeViewPath), PATHINFO_EXTENSION));
    }

    public function render($path, $data): string
    {
        return $this->viewFactory->file($path, $data->all())->render();
    }

    public function renderString($string): string
    {
        return $this->bladeCompiler->compileString($string);
    }

    private function addExtensions(): void
    {
        collect($this->extensionEngines)->each(function ($engine, $extension): void {
            $this->viewFactory->addExtension($extension, $engine);
        });

        collect($this->bladeExtensions)->each(function ($extension): void {
            $this->viewFactory->addExtension($extension, 'php');
            $this->viewFactory->addExtension('blade.' . $extension, 'blade');
        });
    }
}
