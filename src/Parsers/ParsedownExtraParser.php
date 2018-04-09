<?php namespace TightenCo\Jigsaw\Parsers;

use Mni\FrontYAML\Markdown\MarkdownParser;
use ParsedownExtra;

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
