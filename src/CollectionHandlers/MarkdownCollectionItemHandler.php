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

    public function getData($file)
    {
        $document = $this->parser->parseMarkdown($file->getContents());

        return array_merge(['section' => 'content'], $document->frontMatter, ['content' => $document->content]);
    }
}
