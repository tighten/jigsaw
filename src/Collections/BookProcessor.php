<?php

namespace TightenCo\Jigsaw\Collections;

class BookProcessor implements JigsawProcessor
{
    /**
     * Sets a single-line title.
     *
     * @param string $directory A text with a maximum of 24 characters.
     */
    public function process($directory)
    {
        printf("Processing %s with %s\n", $directory, self::class);
    }
}
