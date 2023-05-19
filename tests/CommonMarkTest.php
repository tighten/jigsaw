<?php

namespace Tests;

use League\CommonMark\Extension\Attributes\AttributesExtension;
use TightenCo\Jigsaw\Parsers\MarkdownParserContract;

class CommonMarkTest extends TestCase
{
    /** @test */
    public function enable_commonmark_parser()
    {
        $files = $this->withContent('### Heading {.class}');

        $this->buildSite($files, [
            'commonmark' => true,
        ]);

        $this->assertSame(
            '<div><h3>Heading {.class}</h3></div>',
            $this->clean($files->getChild('build/test.html')->getContent()),
        );
    }

    /** @test */
    public function configure_commonmark_parser()
    {
        $files = $this->withContent('_Em_');

        $this->buildSite($files, [
            'commonmark' => [
                'config' => [
                    'commonmark' => [
                        'enable_em' => false,
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            '<div><p>_Em_</p></div>',
            $this->clean($files->getChild('build/test.html')->getContent()),
        );
    }

    /** @test */
    public function add_commonmark_extensions()
    {
        $files = $this->withContent('### Heading {.class}');

        $this->buildSite($files, [
            'commonmark' => [
                'extensions' => [
                    new AttributesExtension,
                ],
            ],
        ]);

        $this->assertSame(
            '<div><h3 class="class">Heading</h3></div>',
            $this->clean($files->getChild('build/test.html')->getContent()),
        );
    }

    /** @test */
    public function override_parser_with_custom_class()
    {
        $files = $this->withContent('### Heading {.class}');

        $this->app->bind(MarkdownParserContract::class, function () {
            return new class implements MarkdownParserContract {
                public function parse(string $text)
                {
                    return <<<EOT
                        SYKE

                        EOT;
                }
            };
        });

        $this->buildSite($files);

        $this->assertSame(
            '<div>SYKE</div>',
            $this->clean($files->getChild('build/test.html')->getContent()),
        );
    }

    private function withContent(string|array $content)
    {
        return $this->setupSource(array_merge([
            '_layouts' => [
                'master.blade.php' => "<div>@yield('content')</div>",
            ],
            is_string($content) ? ['test.md' => $this->withFrontMatter($content)] : $content,
        ]));
    }

    private function withFrontMatter(string $content): string
    {
        return <<<MD
            ---
            extends: _layouts.master
            section: content
            ---
            {$content}
            MD;
    }
}
