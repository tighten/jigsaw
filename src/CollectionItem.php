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

    public function __get($key)
    {
        return $this->data[$key];
    }

    public function __call($method, $args)
    {
        return $this->helpers[$method]->__invoke(...$args);
    }
}
