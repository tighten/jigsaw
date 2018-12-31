<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Parsers;

use Mni\FrontYAML\Markdown\MarkdownParser;
use ParsedownExtra;

class ParsedownExtraParser implements MarkdownParser
{
    /** @var ParsedownExtra */
    protected $parser;

    public function __construct(ParsedownExtra $parsedownExtra = null)
    {
        $this->parser = $parsedownExtra ?: new ParsedownExtra();
    }

    public function parse($markdown): string
    {
        return $this->parser->parse($markdown);
    }
}
