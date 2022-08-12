<?php

namespace Tests;

class PermalinkTest extends TestCase
{
    /**
     * @test
     */
    public function markdown_file_with_permalink_is_built_at_permalink_destination_when_pretty_urls_is_off()
    {
        $yaml_header = implode("\n", ['---', 'permalink: permalink.html', 'extends: _layouts.master', 'section: content', '---']);
        $files = $this->setupSource([
            '_layouts' => ['master.blade.php' => "<div>@yield('content')</div>"],
            'test.md' => $yaml_header . 'Permalink file',
        ]);
        $this->buildSite($files);

        $this->assertEquals(
            '<div><p>Permalink file</p></div>',
            $this->clean($files->getChild('build/permalink.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function markdown_file_with_permalink_is_built_at_permalink_destination_when_pretty_urls_is_on()
    {
        $yaml_header = implode("\n", ['---', 'permalink: permalink.html', 'extends: _layouts.master', 'section: content', '---']);
        $files = $this->setupSource([
            '_layouts' => ['master.blade.php' => "<div>@yield('content')</div>"],
            'test.md' => $yaml_header . 'Permalink file',
        ]);
        $this->buildSite($files, [], $pretty = true);

        $this->assertEquals(
            '<div><p>Permalink file</p></div>',
            $this->clean($files->getChild('build/permalink.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function markdown_file_with_nested_permalink_is_built_at_permalink_destination()
    {
        $yaml_header = implode("\n", ['---', 'permalink: nested/permalink.html', 'extends: _layouts.master', 'section: content', '---']);
        $files = $this->setupSource([
            '_layouts' => ['master.blade.php' => "<div>@yield('content')</div>"],
            'test.md' => $yaml_header . 'Permalink file',
        ]);
        $this->buildSite($files);

        $this->assertEquals(
            '<div><p>Permalink file</p></div>',
            $this->clean($files->getChild('build/nested/permalink.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function markdown_file_with_nested_permalink_is_built_at_permalink_destination_when_pretty_urls_is_on()
    {
        $yaml_header = implode("\n", ['---', 'permalink: nested/permalink.html', 'extends: _layouts.master', 'section: content', '---']);
        $files = $this->setupSource([
            '_layouts' => ['master.blade.php' => "<div>@yield('content')</div>"],
            'test.md' => $yaml_header . 'Permalink file',
        ]);
        $this->buildSite($files, [], $pretty = true);

        $this->assertEquals(
            '<div><p>Permalink file</p></div>',
            $this->clean($files->getChild('build/nested/permalink.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function permalink_can_contain_leading_slash()
    {
        $yaml_header = implode("\n", ['---', 'permalink: /permalink.html', 'extends: _layouts.master', 'section: content', '---']);
        $files = $this->setupSource([
            '_layouts' => ['master.blade.php' => "<div>@yield('content')</div>"],
            'test.md' => $yaml_header . 'Permalink file',
        ]);
        $this->buildSite($files, [], $pretty = true);

        $this->assertEquals(
            '<div><p>Permalink file</p></div>',
            $this->clean($files->getChild('build/permalink.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function permalink_in_output_paths_contains_leading_slash_if_included_in_permalink()
    {
        $yaml_header = implode("\n", ['---', 'permalink: /permalink.html', 'extends: _layouts.master', 'section: content', '---']);
        $files = $this->setupSource([
            '_layouts' => ['master.blade.php' => "<div>@yield('content')</div>"],
            'test.md' => $yaml_header . 'Permalink file',
        ]);
        $jigsaw = $this->buildSite($files, [], $pretty = true);

        $this->assertEquals('/permalink.html', $jigsaw->getOutputPaths()[0]);
    }

    /**
     * @test
     */
    public function permalink_in_output_paths_contains_leading_slash_if_not_included_in_permalink()
    {
        $yaml_header = implode("\n", ['---', 'permalink: permalink.html', 'extends: _layouts.master', 'section: content', '---']);
        $files = $this->setupSource([
            '_layouts' => ['master.blade.php' => "<div>@yield('content')</div>"],
            'test.md' => $yaml_header . 'Permalink file',
        ]);
        $jigsaw = $this->buildSite($files, [], $pretty = true);

        $this->assertEquals('/permalink.html', $jigsaw->getOutputPaths()[0]);
    }
}
