<?php namespace TightenCo\Jigsaw\CollectionHandlers;

use TightenCo\Jigsaw\FrontMatterParser;

class MarkdownCollectionItemHandler
{
    private $parser;

    public function __construct(FrontMatterParser $parser)
    {
        $this->parser = $parser;
    }

    public function shouldHandle($file)
    {
        return in_array($file->getExtension(), ['markdown', 'md']);
    }

    public function getItemVariables($file)
    {
        $document = $this->parser->parse($file->getContents());

        return array_merge(['section' => 'content'], $document->frontMatter);
    }

    public function getItemContent($file)
    {
        return $this->parser->parseMarkdown($file->getContents())->content;
    }
}
