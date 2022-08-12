<?php

namespace TightenCo\Jigsaw\Parsers;

use Mni\FrontYAML\Markdown\MarkdownParser as FrontYAMLMarkdownParser;

class MarkdownParser implements FrontYAMLMarkdownParser
{
    public $parser;

    public function __construct(JigsawMarkdownParser $parser = null)
    {
        $this->parser = $parser ?: new JigsawMarkdownParser();
    }

    public function __get($property)
    {
        return $this->parser->$property;
    }

    public function __set($property, $value)
    {
        $this->parser->$property = $value;
    }

    public function parse($markdown)
    {
        return $this->parser->parse($markdown);
    }
}
