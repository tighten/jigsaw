<?php namespace TightenCo\Jigsaw;

use Mni\FrontYAML\Parser;

class FrontMatterParser
{
    private $parser;

    public function __construct($parser = null)
    {
        $this->parser = $parser ?: new Parser;
    }

    public function parse($content)
    {
        $document = $this->parser->parse($content, false);
        $frontMatter = $document->getYAML() !== null ? $document->getYAML() : [];
        $documentContent = $document->getContent();
        return [$frontMatter, $documentContent];
    }
}
