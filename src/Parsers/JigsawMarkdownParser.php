<?php

namespace TightenCo\Jigsaw\Parsers;

use Michelf\MarkdownExtra;

class JigsawMarkdownParser extends MarkdownExtra
{
    public function __construct()
    {
        parent::__construct();
        $this->code_class_prefix = 'language-';
        $this->url_filter_func = function ($url) {
            return str_replace("{{'@'}}", '@', $url);
        };
    }

    public function text($text)
    {
        return $this->transform($text);
    }

    public function parse($text)
    {
        return $this->text($text);
    }
}
