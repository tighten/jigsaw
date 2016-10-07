<?php namespace TightenCo\Jigsaw;

// This is sort of hard because people might actually want arrays for some
// things and not objects, since they might need things to be iterable.
//
// Need to think on this.
class ViewData implements \ArrayAccess
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function __get($key)
    {
        if (! isset($this->data[$key])) {
            return null;
        }

        if (is_array($this->data[$key])) {
            return new self($this->data[$key]);
        }
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}
