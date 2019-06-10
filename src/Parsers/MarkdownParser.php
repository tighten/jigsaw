<?php

namespace TightenCo\Jigsaw\Parsers;

use Mni\FrontYAML\Markdown\MarkdownParser as FrontYAMLMarkdownParser;

class MarkdownParser implements FrontYAMLMarkdownParser
{
    public function __construct(JigsawMarkdownParser $parser = null)
    {
        $this->parser = $parser ?: new JigsawMarkdownParser();
    }

    public function parse($markdown)
    {
        return $this->parser->parse($markdown);
    }
}
