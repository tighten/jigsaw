<?php namespace TightenCo\Jigsaw;

use ArrayAccess;
use Exception;
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
        if (! isset($this->items[$key])) {
            $prefix = $this->_source ? 'Error in ' . $this->_source . ': '  : 'Error: ';
            throw new Exception($prefix . "The key '$key' does not exist.");
        }

        $element = $this->items[$key];

        return is_array($element) ? new self($element) : $element;
    }
}
