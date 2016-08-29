<?php namespace TightenCo\Jigsaw;

use Exception;
use TightenCo\Jigsaw\IterableObject;

class CollectionItem extends IterableObject
{
    private $helpers = [];
    private $collection;

    public static function build($collection, $data, $helpers)
    {
        $item = new static($data);
        $item->collection = $collection;
        $item->helpers = $helpers;
        return $item;
    }

    public function next()
    {
        return $this->_nextItem ? $this->collection->get($this->_nextItem) : null;
    }

    public function previous()
    {
        return $this->_previousItem ? $this->collection->get($this->_previousItem) : null;
    }

    public function __call($method, $args)
    {
        return $this->getHelper($method)->__invoke($this, ...$args);
    }

    private function getHelper($name)
    {
        return array_get($this->helpers, $name) ?: function() use ($name) {
            $collection = $this->collection->name;
            throw new Exception("No helper function named '$name' in the collection '$collection'.");
        };
    }
}
