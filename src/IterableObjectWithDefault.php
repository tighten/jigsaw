<?php

namespace TightenCo\Jigsaw;

class IterableObjectWithDefault extends IterableObject
{
    public function __toString()
    {
        return $this->first() ?: '';
    }
}
