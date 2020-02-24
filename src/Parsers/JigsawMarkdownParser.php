<?php

namespace TightenCo\Jigsaw\Parsers;

use Michelf\MarkdownExtra;

class JigsawMarkdownParser extends MarkdownExtra
{
    public function text($text)
    {
        return $this->transform($text);
    }

    public function parse($text)
    {
        return $this->text($text);
    }
}
