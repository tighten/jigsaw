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

    public function getYamlHeader()
    {
        return implode("\n", ['---', 'extends: _layouts.master', 'section: content', '---', '']);
    }
}
