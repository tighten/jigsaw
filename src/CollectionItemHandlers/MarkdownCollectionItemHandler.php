<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\CollectionItemHandlers;

use TightenCo\Jigsaw\Parsers\FrontMatterParser;

class MarkdownCollectionItemHandler
{
    /** @var FrontMatterParser */
    private $parser;

    public function __construct(FrontMatterParser $parser)
    {
        $this->parser = $parser;
    }

    public function shouldHandle($file): bool
    {
        return in_array($file->getExtension(), ['markdown', 'md', 'mdown']);
    }

    public function getItemVariables($file): array
    {
        return $this->parser->parse($file->getContents())->frontMatter;
    }

    public function getItemContent($file): callable
    {
        return function () use ($file): string {
            return $this->parser->parseMarkdown($file->getContents());
        };
    }
}
