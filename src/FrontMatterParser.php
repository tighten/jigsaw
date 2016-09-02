<?php namespace TightenCo\Jigsaw;

use Mni\FrontYAML\Parser;

class FrontMatterParser
{
    private $parser;
    public $frontMatter = [];
    public $content;

    public function __construct($parser = null)
    {
        $this->parser = $parser ?: new Parser;
    }

    public function parseMarkdown($content)
    {
        return $this->parse($content, true);
    }

    public function parse($content, $parseMarkdown = false)
    {
        $document = $this->parser->parse($content, $parseMarkdown);
        $this->frontMatter = $document->getYAML() !== null ? $document->getYAML() : [];
        $this->content = $document->getContent();

        return $this;
    }

    public function getFrontMatter($content)
    {
        return $this->parse($content)->frontMatter;
    }
}
