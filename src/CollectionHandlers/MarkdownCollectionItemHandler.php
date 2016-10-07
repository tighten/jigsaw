<?php namespace TightenCo\Jigsaw\CollectionHandlers;

use Mni\FrontYAML\Parser;
use TightenCo\Jigsaw\CollectionItem;

class MarkdownCollectionItemHandler
{
    private $parser;

    public function __construct($parser = null)
    {
        $this->parser = $parser ?: new Parser;
    }

    public function shouldHandle($file)
    {
        return in_array($file->getExtension(), ['markdown', 'md']);
    }

    public function getData($file)
    {
        $document = $this->parser->parse($file->getContents());

        return array_merge($document->getYAML(), ['content' => $document->getContent()]);
    }
}
