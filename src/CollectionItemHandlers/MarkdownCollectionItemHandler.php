<?php

namespace TightenCo\Jigsaw\CollectionItemHandlers;

use TightenCo\Jigsaw\Parsers\FrontMatterParser;

class MarkdownCollectionItemHandler
{
    private $parser;

    public function __construct(FrontMatterParser $parser)
    {
        $this->parser = $parser;
    }

    public function shouldHandle($file)
    {
        return in_array($file->getExtension(), ['markdown', 'md', 'mdown']);
    }

    public function getItemVariables($file)
    {
        return $this->parser->parse($file->getContents())->frontMatter;
    }

    public function getItemContent($file)
    {
        return function () use ($file) {
            return $this->parser->parseMarkdown($file->getContents());
        };
    }
}
