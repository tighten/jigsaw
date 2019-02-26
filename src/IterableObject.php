<?php

namespace TightenCo\Jigsaw;

use ArrayAccess;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\HigherOrderCollectionProxy;

class IterableObject extends BaseCollection implements ArrayAccess
{
    public function __get($key)
    {
        if (! $this->offsetExists($key) && in_array($key, static::$proxies)) {
            return new HigherOrderCollectionProxy($this, $key);
        }

        return $this->get($key);
    }

    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->getElement($key);
        }

        return value($default);
    }

    public function offsetGet($key)
    {
        if (! isset($this->items[$key])) {
            $prefix = $this->_source ? 'Error in ' . $this->_source . ': ' : 'Error: ';
            throw new Exception($prefix . "The key '$key' does not exist.");
        }

        return $this->getElement($key);
    }

    public function set($key, $value)
    {
        data_set($this->items, $key, $this->isArrayable($value) ? $this->makeIterable($value) : $value);

        if ($first_key = Arr::get(explode('.', $key), 0)) {
            $this->putIterable($first_key, $this->get($first_key));
        }
    }

    public function putIterable($key, $element)
    {
        $this->put($key, $this->isArrayable($element) ? $this->makeIterable($element) : $element);
    }

    protected function getElement($key)
    {
        return $this->items[$key];
    }

    protected function makeIterable($items)
    {
        if ($items instanceof IterableObject) {
            return $items;
        }

        return new IterableObject(collect($items)->map(function ($item) {
            return $this->isArrayable($item) ? $this->makeIterable($item) : $item;
        }));
    }

    protected function isArrayable($element)
    {
        return is_array($element) || $element instanceof BaseCollection;
    }
}
