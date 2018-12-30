<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw;

use ArrayAccess;
use Exception;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\HigherOrderCollectionProxy;

class IterableObject extends BaseCollection implements ArrayAccess
{
    /**
     * @return HigherOrderCollectionProxy|mixed
     */
    public function __get($key)
    {
        if (! $this->offsetExists($key) && in_array($key, static::$proxies)) {
            return new HigherOrderCollectionProxy($this, $key);
        }

        return $this->get($key);
    }

    /**
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->getElement($key);
        }

        return value($default);
    }

    /**
     * @return mixed
     */
    public function offsetGet($key)
    {
        if (! isset($this->items[$key])) {
            $prefix = $this->_source ? 'Error in ' . $this->_source . ': ' : 'Error: ';
            throw new Exception($prefix . "The key '$key' does not exist.");
        }

        return $this->getElement($key);
    }

    public function set($key, $value): void
    {
        data_set($this->items, $key, $this->isArrayable($value) ? $this->makeIterable($value) : $value);

        if ($first_key = array_get(explode('.', $key), 0)) {
            $this->putIterable($first_key, $this->get($first_key));
        }
    }

    public function putIterable($key, $element): void
    {
        $this->put($key, $this->isArrayable($element) ? $this->makeIterable($element) : $element);
    }

    /**
     * @return mixed
     */
    protected function getElement($key)
    {
        return $this->items[$key];
    }

    protected function makeIterable($items): IterableObject
    {
        if ($items instanceof IterableObject) {
            return $items;
        }

        return new IterableObject(collect($items)->map(function ($item) {
            return $this->isArrayable($item) ? $this->makeIterable($item) : $item;
        }));
    }

    protected function isArrayable($element): bool
    {
        return is_array($element) || $element instanceof BaseCollection;
    }
}
