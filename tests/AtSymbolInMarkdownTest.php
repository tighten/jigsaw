<?php

namespace Tests;

use TightenCo\Jigsaw\IterableObject;

class AtSymbolInMarkdownTest extends TestCase
{
    /**
     * @test
     */
    public function mailto_link_in_markdown_is_parsed()
    {
        $files = $this->setupSource([
            '_layouts' => [
                'master.blade.php' => "<div>@yield('content')</div>",
            ],
            'test.md' => $this->getYamlHeader() .
                '[test@test.com](mailto:test@test.com)',
        ]);
        $this->buildSite($files);

        $this->assertEquals(
            '<div><p><a href="mailto:test@test.com">test@test.com</a></p></div>',
            $files->getChild('build/test.html')->getContent()
        );
    }

    /**
     * @test
     */
    public function mailto_link_in_blade_markdown_is_parsed()
    {
        $files = $this->setupSource([
            '_layouts' => [
                'master.blade.php' => "<div>@yield('content')</div>",
            ],
            'test.blade.md' => $this->getYamlHeader() .
                '[test@test.com](mailto:test@test.com)',
        ]);
        $this->buildSite($files);

        $this->assertEquals(
            '<div><p><a href="mailto:test@test.com">test@test.com</a></p></div>',
            $files->getChild('build/test.html')->getContent()
        );
    }

    public function getYamlHeader()
    {
        return implode("\n", ['---', 'extends: _layouts.master', 'section: content', '---', '']);
    }
}
