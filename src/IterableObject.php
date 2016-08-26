<?php namespace TightenCo\Jigsaw;

use ArrayAccess;
use Illuminate\Support\Collection as BaseCollection;

class IterableObject extends BaseCollection implements ArrayAccess
{
    public function __get($key)
    {
        if (! $this->has($key)) {
            return;
        }

        $element = $this->get($key);

        return is_array($element) ? new self($element) : $element;
    }

    public function offsetGet($key)
    {
        $element = $this->items[$key];

        return is_array($element) ? new self($element) : $element;
    }
}
