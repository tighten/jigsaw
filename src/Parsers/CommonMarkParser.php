<?php

namespace TightenCo\Jigsaw\Parsers;

use Illuminate\Support\Arr;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;

class CommonMarkParser implements MarkdownParserContract
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment(Arr::get(app('config'), 'commonmark.config', []));

        $environment->addExtension(new CommonMarkCoreExtension);

        collect(Arr::get(app('config'), 'commonmark.extensions', [
            new AttributesExtension,
            new SmartPunctExtension,
            new StrikethroughExtension,
            new TableExtension,
        ]))->map(fn ($extension) => $environment->addExtension($extension));

        collect(
            Arr::get(app('config'), 'commonmark.renderers')
        )->map(fn ($renderer, $nodeClass) => $environment->addRenderer($nodeClass, $renderer));

        $this->converter = new MarkdownConverter($environment);
    }

    public function parse(string $text)
    {
        return $this->converter->convert($text);
    }
}
