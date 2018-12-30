<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw;

class IterableObjectWithDefault extends IterableObject
{
    public function __toString(): string
    {
        return $this->first() ?: '';
    }
}
