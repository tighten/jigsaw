<?php

namespace Tests;

class CollectionItemTest extends TestCase
{
    /**
     * @test
     */
    public function collection_item_contents_are_returned_when_item_is_referenced_as_a_string()
    {
        $config = collect(['collections' => ['collection' => []]]);
        $yaml_header = implode("\n", ['---', 'section: content', '---']);
        $files = $this->setupSource([
            '_collection' => [
                'item.md' => $yaml_header . '### Collection Item Content',
            ],
            'test_get_content.blade.php' => '<div>{!! $collection->first()->getContent() !!}</div>',
            'test_to_string.blade.php' => '<div>{!! $collection->first() !!}</div>',
        ]);

        $this->buildSite($files, $config);

        $this->assertEquals(
            $files->getChild('build/test_get_content.html')->getContent(),
            $files->getChild('build/test_to_string.html')->getContent()
        );
        $this->assertEquals(
            '<div><h3>Collection Item Content</h3></div>',
            $files->getChild('build/test_to_string.html')->getContent()
        );
    }

    public function test_drafts_are_hidden_in_collection_item_and_gets_compiled_when_config_has_drafts_set_to_false()
    {
        $config = collect(['collections' => ['collection' => []], 'drafts' => false]);
        $yaml_header = implode("\n", ['---', 'extends: _layouts.post', 'section: content', '---']);
        $files = $this->setupSource([
            '_layouts' => [
                'post.blade.php' => "<body>@yield('content')</body>",
            ],
            '_collection' => [
                'collection-item.md' => $yaml_header . '### Collection Item Content',
                '_drafts' => [
                    'draft.md' => $yaml_header . '### Simple Draft',
                ],
            ],
            'test_to_string.blade.php' => '<div>@foreach($collection as $item){!! $item->getContent() !!}@endforeach</div>',
        ]);

        $this->buildSite($files, $config);

        $this->assertTrue($files->hasChild('build/collection/collection-item.html'));
        $this->assertFalse($files->hasChild('build/collection/draft.html'));

        $this->assertEquals(
            '<div><h3>Collection Item Content</h3></div>',
            $files->getChild('build/test_to_string.html')->getContent()
        );
    }

    public function test_drafts_are_shown_in_collection_item_and_gets_compiled_when_config_has_drafts_set_to_true()
    {
        $config = collect(['collections' => ['collection' => []], 'drafts' => true]);
        $yaml_header = implode("\n", ['---', 'extends: _layouts.post', 'section: content', '---']);
        $files = $this->setupSource([
            '_layouts' => [
                'post.blade.php' => "<body>@yield('content')</body>",
            ],
            '_collection' => [
                'collection-item.md' => $yaml_header . '### Collection Item Content',
                '_drafts' => [
                    'draft.md' => $yaml_header . '### Simple Draft',
                ],
            ],
            'test_to_string.blade.php' => '<div>@foreach($collection as $item){!! $item->getContent() !!}@endforeach</div>',
        ]);

        $this->buildSite($files, $config);

        $this->assertTrue($files->hasChild('build/collection/collection-item.html'));
        $this->assertTrue($files->hasChild('build/collection/draft.html'));

        $this->assertEquals(
            '<div><h3>Collection Item Content</h3><h3>Simple Draft</h3></div>',
            $files->getChild('build/test_to_string.html')->getContent()
        );
    }
}
