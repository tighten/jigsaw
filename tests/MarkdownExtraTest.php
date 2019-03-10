<?php

namespace Tests;

class MarkdownExtraTest extends TestCase
{
    /**
     * @test
     */
    public function parse_markdown_inside_html_blocks()
    {
        $files = $this->setupSource([
            '_layouts' => [
                'master.blade.php' => "<div>@yield('content')</div>",
            ],
            'test.md' => $this->getYamlHeader() . '<div markdown="1">This is *true* markdown text.</div>',
        ]);
        $this->buildSite($files);

        $this->assertEquals(
            "<div><div>\n<p>This is <em>true</em> markdown text.</p>\n</div></div>",
            $files->getChild('build/test.html')->getContent()
        );
    }

    /**
     * @test
     */
    public function can_specify_id_in_markdown()
    {
        $files = $this->setupSource([
            '_layouts' => [
                'master.blade.php' => "<div>@yield('content')</div>",
            ],
            'test.md' => $this->getYamlHeader() . '### Testing ID ### {#test-id}',
        ]);
        $this->buildSite($files);

        $this->assertEquals(
            '<div><h3 id="test-id">Testing ID</h3></div>',
            $files->getChild('build/test.html')->getContent()
        );
    }

    /**
     * @test
     */
    public function can_specify_internal_anchor_links_in_markdown()
    {
        $files = $this->setupSource([
            '_layouts' => [
                'master.blade.php' => "<div>@yield('content')</div>",
            ],
            'test.md' => $this->getYamlHeader() . '[Link back to header 1](#header1)',
        ]);
        $this->buildSite($files);

        $this->assertEquals(
            '<div><p><a href="#header1">Link back to header 1</a></p></div>',
            $files->getChild('build/test.html')->getContent()
        );
    }

    /**
     * @test
     */
    public function can_specify_class_name_in_markdown()
    {
        $files = $this->setupSource([
            '_layouts' => [
                'master.blade.php' => "<div>@yield('content')</div>",
            ],
            'test.md' => $this->getYamlHeader() . '### Testing class ### {.test-class}',
        ]);
        $this->buildSite($files);

        $this->assertEquals(
            '<div><h3 class="test-class">Testing class</h3></div>',
            $files->getChild('build/test.html')->getContent()
        );
    }

    /**
     * @test
     */
    public function correctly_parse_single_line_html_markup_in_markdown_file()
    {
        $files = $this->setupSource([
            '_layouts' => [
                'master.blade.php' => "<div>@yield('content')</div>",
            ],
            'multi-line.md' => $this->getYamlHeader() .
                '<h1>Header 1</h1>' . "\n" . '<h2>Header 2</h2>',
            'single-line.md' => $this->getYamlHeader() .
                '<h1>Header 1</h1><h2>Header 2</h2>',
            'single-line-with-space.md' => $this->getYamlHeader() .
                '<h1>Header 1</h1> <h2>Header 2</h2>',
            'nested.md' => $this->getYamlHeader() .
                '<p><strong>Contact Method:</strong> email</p><p>Test</p><p><em>Some italic text.</em></p>',
        ]);

        $this->buildSite($files);

        $this->assertEquals(
            "<div><h1>Header 1</h1>\n<h2>Header 2</h2></div>",
            $files->getChild('build/multi-line.html')->getContent()
        );

        $this->assertEquals(
            "<div><h1>Header 1</h1>\n<h2>Header 2</h2></div>",
            $files->getChild('build/single-line.html')->getContent()
        );

        $this->assertEquals(
            "<div><h1>Header 1</h1> <h2>Header 2</h2></div>",
            $files->getChild('build/single-line-with-space.html')->getContent()
        );

        $this->assertEquals(
            "<div><p><strong>Contact Method:</strong> email</p>\n<p>Test</p>\n<p><em>Some italic text.</em></p></div>",
            $files->getChild('build/nested.html')->getContent()
        );
    }

    public function getYamlHeader()
    {
        return implode("\n", ['---', 'extends: _layouts.master', 'section: content', '---']);
    }
}
