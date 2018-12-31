<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\CollectionItemHandlers;

use TightenCo\Jigsaw\Parsers\FrontMatterParser;

class BladeCollectionItemHandler
{
    /** @var FrontMatterParser */
    private $parser;

    public function __construct(FrontMatterParser $parser)
    {
        $this->parser = $parser;
    }

    public function shouldHandle($file): bool
    {
        return str_contains($file->getFilename(), '.blade.');
    }

    public function getItemVariables($file): array
    {
        $content = $file->getContents();
        $frontMatter = $this->parser->getFrontMatter($content);
        $extendsFromBladeContent = $this->parser->getExtendsFromBladeContent($content);

        return array_merge(
            $frontMatter,
            ['extends' => $extendsFromBladeContent ?: array_get($frontMatter, 'extends')]
        );
    }

    public function getItemContent($file): void
    {
        return;
    }
}
