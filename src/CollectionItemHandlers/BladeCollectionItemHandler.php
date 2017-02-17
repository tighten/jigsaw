<?php namespace TightenCo\Jigsaw\CollectionItemHandlers;

use TightenCo\Jigsaw\Parsers\FrontMatterParser;

class BladeCollectionItemHandler
{
    private $parser;

    public function __construct(FrontMatterParser $parser)
    {
        $this->parser = $parser;
    }

    public function shouldHandle($file)
    {
        return str_contains($file->getFilename(), '.blade.');
    }

    public function getItemVariables($file)
    {
        $content = $file->getContents();
        $frontMatter = collect($this->parser->getFrontMatter($content));
        $extendsFromBladeContent = $this->parser->getExtendsFromBladeContent($content);

        return array_merge(
            $frontMatter->all(),
            ['extends' => $extendsFromBladeContent ?: $frontMatter->get('extends')]
        );
    }

    public function getItemContent($file)
    {
        return;
    }

    private function getCollectionName($file)
    {
        return substr($file->topLevelDirectory(), 1);
    }
}
