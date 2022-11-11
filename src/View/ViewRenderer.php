<?php

namespace TightenCo\Jigsaw\View;

/**
 * For now some of this only works if we do it in this file, not in the ViewServiceProvider,
 * because other code (BuildCommand, TestCase) updates the config or buildPaths after the
 * ViewServiceProvider is registered, so if we do it there it'll be overwritten.
 */
class ViewRenderer
{
    public function __construct()
    {
        $this->addExtensions();
        $this->addHintpaths();
    }

    public function getExtension($bladeViewPath)
    {
        return strtolower(pathinfo(app('view')->getFinder()->find($bladeViewPath), PATHINFO_EXTENSION));
    }

    public function render($path, $data)
    {
        return app('view')->file($path, $data->all())->render();
    }

    public function renderString($string)
    {
        return app('blade.compiler')->compileString($string);
    }

    private function addHintpaths()
    {
        foreach (app('config')->get('viewHintPaths', []) as $hint => $path) {
            app('view')->addNamespace($hint, $path);
        }
    }

    private function addExtensions()
    {
        foreach (['md', 'markdown', 'mdown'] as $extension) {
            app('view')->addExtension($extension, 'markdown');
            app('view')->addExtension("blade.{$extension}", 'blade-markdown');
        }

        foreach (['js', 'json', 'xml', 'yaml', 'yml', 'rss', 'atom', 'txt', 'text', 'html'] as $extension) {
            app('view')->addExtension($extension, 'php');
            app('view')->addExtension("blade.{$extension}", 'blade');
        }
    }
}
