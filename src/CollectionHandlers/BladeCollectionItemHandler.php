<?php namespace TightenCo\Jigsaw\CollectionHandlers;

use TightenCo\Jigsaw\FrontMatterParser;

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
        $contents = $file->getContents();
        $frontMatter = collect($this->parser->getFrontMatter($contents));

        if (! $frontMatter->has('extends')) {
            $bladeExtends = $this->extractExtendsFromBlade($contents);
        }

        return array_merge(
            ['section' => 'content'],
            $frontMatter->all(),
            isset($bladeExtends) ? ['extends' => $bladeExtends] : []
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

    private function extractExtendsFromBlade($contents)
    {
        preg_match('/@extends\s*\(\s*[\"|\']\s*(.+?)\s*[\"|\']\s*\)/', $contents, $matches);

        return isset($matches[1]) ? $matches[1] : null;
    }
}
