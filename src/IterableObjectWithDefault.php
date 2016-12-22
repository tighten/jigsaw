<?php namespace TightenCo\Jigsaw;

use TightenCo\Jigsaw\IterableObject;

class IterableObjectWithDefault extends IterableObject
{
    public function __toString()
    {
        return $this->first() ?: '';
    }
}
