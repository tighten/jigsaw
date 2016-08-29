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
        $data = $this->addMeta($data);

        return $this->viewFactory->file(
            $path,
            array_merge(['jigsaw' => $data], $data->all())
        )->render();
    }

    private function addMeta($data)
    {
        $data['path'] = trim(array_get($data, 'link'), '/');
        $data['url'] = rtrim(array_get($data, 'url'), '/') . '/' . trim(array_get($data, 'link'), '/');

        return $data;
    }
}
