<?php namespace TightenCo\Jigsaw;

use ArrayAccess;
use Exception;

class CollectionItem implements ArrayAccess
{
    private $data = [];
    private $helpers = [];

    public function __construct($data, $helpers)
    {
        $this->data = $data;
        $this->helpers = $helpers;
    }

    public function __get($key)
    {
        return $this->offsetExists($key) ? $this->offsetGet($key) : null;
    }

    public function __call($method, $args)
    {
        return $this->getHelper($method)->__invoke($this, ...$args);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    private function getHelper($name)
    {
        return array_get($this->helpers, $name) ?: function() use ($name) {
            throw new Exception("No helper function named '$name' in the collection '$this->name'.");
        };
    }
}
