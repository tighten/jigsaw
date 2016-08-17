<?php namespace TightenCo\Jigsaw;

class CollectionItem
{
    private $data;
    private $helpers;

    public function __construct($data, $helpers)
    {
        $this->data = $data;
        $this->helpers = $helpers;
    }

    public function getFilename()
    {
        return $this->data['filename'];
    }

    public function getLink()
    {
        return $this->data['link'];
    }

    public function __get($key)
    {
        return $this->data[$key];
    }

    public function __call($method, $args)
    {
        return $this->helpers[$method]->__invoke($this->data, ...$args);
    }
}
