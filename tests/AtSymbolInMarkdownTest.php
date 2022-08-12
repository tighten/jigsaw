<?php

namespace Tests;

class AtSymbolInMarkdownTest extends TestCase
{
    /**
     * @test
     */
    public function mailto_link_in_markdown_is_parsed_and_obfuscated()
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
            '<div><p><a href="&#109;&#x61;&#105;&#x6c;&#116;&#x6f;&#58;&#x74;&#101;&#x73;&#116;&#x40;&#116;&#x65;&#115;&#x74;&#46;&#x63;&#111;&#x6d;">test@test.com</a></p></div>',
            $this->clean($files->getChild('build/test.html')->getContent())
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
            '<div><p><a href="&#109;&#x61;&#105;&#x6c;&#116;&#x6f;&#58;&#x74;&#101;&#x73;&#116;&#x40;&#116;&#x65;&#115;&#x74;&#46;&#x63;&#111;&#x6d;">test@test.com</a></p></div>',
            $this->clean($files->getChild('build/test.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function at_symbol_after_closing_bracket_is_unchanged_in_markdown()
    {
        $files = $this->setupSource([
            '_layouts' => [
                'master.blade.php' => "<div>@yield('content')</div>",
            ],
            'test.md' => $this->getYamlHeader() .
                "<p>@include('foo')</p>"
        ]);
        $this->buildSite($files);

        $this->assertEquals(
            "<div><p>@include('foo')</p></div>",
            $this->clean($files->getChild('build/test.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function double_at_symbol_in_fenced_code_block_is_parsed_to_single_at_symbol_in_blade_markdown()
    {
        $files = $this->setupSource([
            '_layouts' => [
                'master.blade.php' => "<div>@yield('content')</div>",
            ],
            'test.blade.md' => $this->getYamlHeader() .
                "```\n@@if(true)<h1>Foo</h1>@@endif\n```"
        ]);
        $this->buildSite($files);

        $this->assertEquals(
            '<div><pre><code>@if(true)&lt;h1&gt;Foo&lt;/h1&gt;@endif</code></pre></div>',
            $this->clean($files->getChild('build/test.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function double_at_symbol_in_inline_code_block_is_parsed_to_single_at_symbol_in_blade_markdown()
    {
        $files = $this->setupSource([
            '_layouts' => [
                'master.blade.php' => "<div>@yield('content')</div>",
            ],
            'test.blade.md' => $this->getYamlHeader() .
                "`@@if(true)<h1>Foo</h1>@@endif`"
        ]);
        $this->buildSite($files);

        $this->assertEquals(
            '<div><p><code>@if(true)&lt;h1&gt;Foo&lt;/h1&gt;@endif</code></p></div>',
            $this->clean($files->getChild('build/test.html')->getContent())
        );
    }

    public function getYamlHeader()
    {
        return implode("\n", ['---', 'extends: _layouts.master', 'section: content', '---', '']);
    }
}
