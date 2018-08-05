<?php

namespace TightenCo\Jigsaw\Parsers;

use ParsedownExtra;
use Mni\FrontYAML\Markdown\MarkdownParser;

class ParsedownExtraParser implements MarkdownParser
{
    public function __construct(ParsedownExtra $parsedownExtra = null)
    {
        $this->parser = $parsedownExtra ?: new ParsedownExtra();
    }

    public function parse($markdown)
    {
        return $this->parser->parse($markdown);
    }
}
