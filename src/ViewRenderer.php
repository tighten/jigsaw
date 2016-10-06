<?php namespace TightenCo\Jigsaw;

use Illuminate\View\Factory;

class ViewRenderer
{
    private $viewFactory;
    private $extensionEngines = [
        'md' => 'markdown',
        'markdown' => 'markdown',
        'blade.md' => 'blade-markdown',
        'blade.markdown' => 'blade-markdown',
    ];
    private $bladeExtensions = [
        'js', 'json', 'xml', 'rss', 'txt', 'text', 'html'
    ];

    public function __construct(Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
        $this->finder = $this->viewFactory->getFinder();
        $this->addExtensions();
    }

    public function getExtension($bladeViewPath)
    {
        return strtolower(pathinfo($this->finder->find($bladeViewPath), PATHINFO_EXTENSION));
    }

    public function render($path, $data)
    {
        $data = $this->updateMetaForCollectionItem($data);

        return $this->viewFactory->file($path, $data->all())->render();
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

    private function updateMetaForCollectionItem($data)
    {
        if ($data->item) {
            $data->path = $data->item->path;
            $data->url = $data->item->url;
        }

        return $data;
    }
}
