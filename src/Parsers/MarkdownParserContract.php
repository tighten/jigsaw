<?php

namespace TightenCo\Jigsaw\Parsers;

interface MarkdownParserContract
{
    public function parse(string $text);
}
