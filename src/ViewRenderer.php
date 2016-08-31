<?php namespace TightenCo\Jigsaw;

use Illuminate\View\Factory;

class ViewRenderer
{
    private $viewFactory;
    private $allowedBladeExtensions = [
        'js', 'json', 'xml', 'rss', 'txt', 'text', 'html'
    ];

    public function __construct(Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
        $this->finder = $this->viewFactory->getFinder();
        $this->addBladeExtensions();
    }

    private function addBladeExtensions()
    {
        collect($this->allowedBladeExtensions)->each(function ($extension) {
            $this->viewFactory->addExtension('blade.' . $extension, 'blade');
        });
    }

    public function render($path, $data)
    {
        $data = $this->updateMetaForCollectionItem($data);

        return $this->viewFactory->file(
            $path,
            array_merge(['jigsaw' => $data], $data->all())
        )->render();
    }

    public function getExtension($bladeViewPath)
    {
        return strtolower(pathinfo($this->finder->find($bladeViewPath), PATHINFO_EXTENSION));
    }

    private function updateMetaForCollectionItem($data)
    {
        if ($data->item) {
            $data->link = $data->item->link;
            $data->path = $data->item->path;
            $data->url = $data->item->url;
        }

        return $data;
    }
}
