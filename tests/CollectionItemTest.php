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

    /**
     * @test
     */
    public function collection_item_can_be_filtered()
    {
        $config = collect(['collections' =>
            [
                'collection' => [
                    'path' => 'collection/{filename}',
                    'filter' => function ($item) {
                        return $item->published;
                    }
                ]
            ]
        ]);
        $builtHeader = implode("\n", [
            '---',
            'extends: _layouts.collection_item',
            'published: true',
            'section: content',
            '---'
        ]);
        $filteredHeader = implode("\n", [
            '---',
            'extends: _layouts.collection_item',
            'published: false',
            'section: content',
            '---'
        ]);
        $files = $this->setupSource([
            '_layouts' => [
                'collection_item.blade.php' => '@section(\'content\') @endsection'
            ],
            '_collection' => [
                'item.md' => implode("\n", [$builtHeader, '### Collection Item Content']),
                'filtered.md' => implode("\n", [$filteredHeader, '### Filtered Item Content']),
            ],
        ]);

        $jigsaw = $this->buildSite($files, $config);

        $this->assertNull($jigsaw->getSiteData()->collection->filtered);
        $this->assertNotNull($jigsaw->getSiteData()->collection->item);

        $this->assertNull($files->getChild('build/collection/filtered.html'));
        $this->assertNotNull($files->getChild('build/collection/item.html'));
    }
}
