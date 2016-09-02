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

    public function setContent($content)
    {
        $this->_content = $content;
    }

    public function getContent()
    {
        return $this->_content;
    }

    public function __call($method, $args)
    {
        return $this->getHelper($method)->__invoke($this, ...$args);
    }

    private function getHelper($name)
    {
        return array_get($this->helpers, $name) ?: function() use ($name) {
            $collection = $this->get('collection');
            throw new Exception('No helper function named "' . $name. '" for the collection "' . $this->get('collection') . '" was found in the file "collections.php".');
        };
    }
}
