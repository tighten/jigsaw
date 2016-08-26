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

    public function parse($content)
    {
        $document = $this->parser->parse($content, false);
        $this->frontMatter = $document->getYAML() !== null ? $document->getYAML() : [];
        $this->content = $document->getContent();

        return $this;
    }
}
