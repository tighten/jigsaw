<?php

namespace TightenCo\Jigsaw\Parsers;

use Illuminate\Support\Arr;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

class CommonMarkParser implements MarkdownParserContract
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment(Arr::get(app('config'), 'commonmark.config', []));

        $environment->addExtension(new CommonMarkCoreExtension);

        collect(Arr::get(app('config'), 'commonmark.extensions', []))
            ->map(fn ($extension) => $environment->addExtension($extension));

        $this->converter = new MarkdownConverter($environment);
    }

    public function parse(string $text)
    {
        return $this->converter->convert($text);
    }
}
