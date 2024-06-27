<?php

namespace TightenCo\Jigsaw\Parsers;

use Mni\FrontYAML\Markdown\MarkdownParser as FrontYAMLMarkdownParserInterface;

class MarkdownParser implements FrontYAMLMarkdownParserInterface
{
    public $parser;

    public function __construct(?MarkdownParserContract $parser = null)
    {
        $this->parser = $parser ?? new JigsawMarkdownParser;
    }

    public function __get($property)
    {
        return $this->parser->$property;
    }

    public function __set($property, $value)
    {
        $this->parser->$property = $value;
    }

    public function parse($markdown): string
    {
        return $this->parser->parse($markdown);
    }
}
