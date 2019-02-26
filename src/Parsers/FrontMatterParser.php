<?php

namespace TightenCo\Jigsaw\Parsers;

use Illuminate\Support\Arr;
use Mni\FrontYAML\Parser;

class FrontMatterParser
{
    private $parser;
    public $frontMatter = [];
    public $content;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parseMarkdown($content)
    {
        return $this->parse($content, true)->content;
    }

    public function parseMarkdownWithoutFrontMatter($content)
    {
        return $this->parser->parse($this->extractContent($content))->getContent();
    }

    public function parse($content, $parseMarkdown = false)
    {
        $document = $this->parser->parse($content, $parseMarkdown);
        $this->frontMatter = $document->getYAML() !== null ? $document->getYAML() : [];
        $this->content = $document->getContent();

        return $this;
    }

    public function getFrontMatter($content)
    {
        return $this->parse($content)->frontMatter;
    }

    public function getContent($content)
    {
        return $this->parse($content, false)->content;
    }

    public function getBladeContent($content)
    {
        $parsed = $this->parse($content);
        $extendsFromFrontMatter = Arr::get($parsed->frontMatter, 'extends');

        return (! $this->getExtendsFromBladeContent($parsed->content) && $extendsFromFrontMatter) ?
            $this->addExtendsToBladeContent($extendsFromFrontMatter, $parsed->content) :
            $parsed->content;
    }

    public function getExtendsFromBladeContent($content)
    {
        preg_match('/@extends\s*\(\s*[\"|\']\s*(.+?)\s*[\"|\']\s*\)/', $content, $matches);

        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Adapted from Mni\FrontYAML.
     */
    public function extractContent($content)
    {
        $regex = '~^('
            . '---'                                  // $matches[1] start separator
            . "){1}[\r\n|\n]*(.*?)[\r\n|\n]+("       // $matches[2] front matter
            . '---'                                  // $matches[3] end separator
            . "){1}[\r\n|\n]*(.*)$~s";               // $matches[4] document content

        return preg_match($regex, $content, $matches) === 1 ? ltrim($matches[4]) : $content;
    }

    private function addExtendsToBladeContent($extends, $bladeContent)
    {
        return "@extends('$extends')\n" . $bladeContent;
    }
}
