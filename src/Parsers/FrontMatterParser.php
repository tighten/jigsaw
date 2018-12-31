<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Parsers;

use Mni\FrontYAML\Parser;

class FrontMatterParser
{
    /** @var Parser */
    private $parser;

    /** @var array */
    public $frontMatter = [];

    /** @var string */
    public $content;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parseMarkdown($content): string
    {
        return $this->parse($content, true)->content;
    }

    public function parseMarkdownWithoutFrontMatter($content): string
    {
        return $this->parser->parse($this->extractContent($content))->getContent();
    }

    public function parse($content, $parseMarkdown = false): FrontMatterParser
    {
        $document = $this->parser->parse($content, $parseMarkdown);
        $this->frontMatter = $document->getYAML() !== null ? $document->getYAML() : [];
        $this->content = $document->getContent();

        return $this;
    }

    public function getFrontMatter($content): array
    {
        return $this->parse($content)->frontMatter;
    }

    public function getContent($content): string
    {
        return $this->parse($content, false)->content;
    }

    public function getBladeContent($content): string
    {
        $parsed = $this->parse($content);
        $extendsFromFrontMatter = array_get($parsed->frontMatter, 'extends');

        return (! $this->getExtendsFromBladeContent($parsed->content) && $extendsFromFrontMatter) ?
            $this->addExtendsToBladeContent($extendsFromFrontMatter, $parsed->content) :
            $parsed->content;
    }

    public function getExtendsFromBladeContent($content): ?string
    {
        preg_match('/@extends\s*\(\s*[\"|\']\s*(.+?)\s*[\"|\']\s*\)/', $content, $matches);

        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Adapted from Mni\FrontYAML.
     */
    public function extractContent($content): string
    {
        $regex = '~^('
            . '---'                                  // $matches[1] start separator
            . "){1}[\r\n|\n]*(.*?)[\r\n|\n]+("       // $matches[2] front matter
            . '---'                                  // $matches[3] end separator
            . "){1}[\r\n|\n]*(.*)$~s";               // $matches[4] document content

        return preg_match($regex, $content, $matches) === 1 ? ltrim($matches[4]) : $content;
    }

    private function addExtendsToBladeContent($extends, $bladeContent): string
    {
        return "@extends('$extends')\n" . $bladeContent;
    }
}
