<?php

namespace Tests;

use TightenCo\Jigsaw\Handlers\MarkdownHandler;
use TightenCo\Jigsaw\IterableObject;
use TightenCo\Jigsaw\PageData;

class MarkdownExtraTest extends TestCase
{
    public function test_parse_markdown_inside_html_blocks()
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

    public function test_can_specify_id_in_markdown()
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

    public function test_can_specify_internal_anchor_links_in_markdown()
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

    public function test_can_specify_class_name_in_markdown()
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

    public function getYamlHeader()
    {
        return implode("\n", ['---', 'extends: _layouts.master', 'section: content', '---']);
    }
}
