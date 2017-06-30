<?php namespace TightenCo\Jigsaw\View;

use Illuminate\View\Factory;

class ViewRenderer
{
    private $viewFactory;
    private $minifyHtml;
    private $extensionEngines = [
        'md' => 'markdown',
        'markdown' => 'markdown',
        'blade.md' => 'blade-markdown',
        'blade.markdown' => 'blade-markdown',
    ];
    private $bladeExtensions = [
        'js', 'json', 'xml', 'rss', 'txt', 'text', 'html'
    ];

    public function __construct(Factory $viewFactory, $minifyHtml = false)
    {
        $this->viewFactory = $viewFactory;
        $this->minifyHtml = $minifyHtml;
        $this->finder = $this->viewFactory->getFinder();
        $this->addExtensions();
    }

    public function getExtension($bladeViewPath)
    {
        return strtolower(pathinfo($this->finder->find($bladeViewPath), PATHINFO_EXTENSION));
    }

    public function render($path, $data)
    {
        $minifyHtmlCallback = null;
        if ($this->minifyHtml) {
            $minifyHtmlCallback = function ($view, $contents) {
                 $search = array(
                    '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
                    '/[^\S ]+\</s',     // strip whitespaces before tags, except space
                    '/(\s)+/s',         // shorten multiple whitespace sequences
                    '/<!--(.|\s)*?-->/' // Remove HTML comments
                );

                $replace = array(
                    '>',
                    '<',
                    '\\1',
                    ''
                );

                $buffer = preg_replace($search, $replace, $contents);

                return $buffer;
            };
        }

        return $this->viewFactory->file($path, $data->all())->render($minifyHtmlCallback);
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
