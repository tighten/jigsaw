<?php namespace TightenCo\Jigsaw;

use Illuminate\View\Factory;

class ViewRenderer
{
    private $viewFactory;

    public function __construct(Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    public function render($path, $data)
    {
        $data = $this->updateMetaForCollectionItem($data);

        return $this->viewFactory->file(
            $path,
            array_merge(['jigsaw' => $data], $data->all())
        )->render();
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
