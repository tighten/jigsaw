<?php

namespace TightenCo\Jigsaw\Parsers;

use Jampire\Markdown\Toc\MarkdownExtra;

class JigsawMarkdownParser extends MarkdownExtra
{
    public function text($text)
    {
        return self::defaultTransform($text);
    }

    public function parse($text)
    {
        return $this->text($text);
    }
}
