<?php

namespace Tests;

use TightenCo\Jigsaw\Collection\CollectionItem;

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
            $this->clean($files->getChild('build/test_to_string.html')->getContent())
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

    /**
     * @test
     */
    public function collection_item_can_be_mapped()
    {
        $config = collect(['collections' =>
            [
                'collection' => [
                    'path' => 'collection/{filename}',
                    'map' => function ($item) {
                        return MappedItem::fromItem($item);
                    }
                ]
            ]
        ]);
        $itemHeader = implode("\n", [
            '---',
            'number: 111',
            '---',
        ]);
        $files = $this->setupSource([
            '_layouts' => [
                'item.blade.php' => '@section(\'content\') @endsection',
            ],
            '_collection' => [
                'item.blade.php' => implode("\n", [
                    $itemHeader,
                    "@extends('_layouts.item')",
                    '{{ $page->number }}-{{ $page->doubleNumber() }}',
                ]),
            ],
        ]);

        $jigsaw = $this->buildSite($files, $config);

        $this->assertEquals(
            '111-222',
            $this->clean($files->getChild('build/collection/item.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function collection_item_page_metadata_contains_path()
    {
        $config = collect(['collections' => ['collection' => []]]);
        $files = $this->setupSource([
            '_layouts' => [
                'item.blade.php' => '@section(\'content\') @endsection',
            ],
            '_collection' => [
                'page.blade.php' => "@extends('_layouts.item')\n" . '{{ $page->getPath() }}',
            ],
        ]);

        $this->buildSite($files, $config, $pretty = true);

        $this->assertEquals(
            '/collection/page',
            $this->clean($files->getChild('build/collection/page/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function collection_item_page_metadata_contains_relative_path()
    {
        $config = collect(['collections' => ['collection' => []]]);
        $files = $this->setupSource([
            '_layouts' => [
                'item.blade.php' => '@section(\'content\') @endsection',
            ],
            '_collection' => [
                'nested' => [
                    'page.blade.php' => "@extends('_layouts.item')\n" . '{{ $page->getRelativePath() }}',
                ],
            ],
        ]);

        $this->buildSite($files, $config, $pretty = true);

        $this->assertEquals(
            'nested',
            $this->clean($files->getChild('build/collection/page/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function collection_item_page_metadata_contains_extension()
    {
        $config = collect(['collections' => ['collection' => []]]);
        $files = $this->setupSource([
            '_layouts' => [
                'item.blade.php' => '@section(\'content\') @endsection',
            ],
            '_collection' => [
                'page.blade.php' => "@extends('_layouts.item')\n" . '{{ $page->getExtension() }}',
            ],
        ]);

        $this->buildSite($files, $config, $pretty = true);

        $this->assertEquals(
            'blade.php',
            $this->clean($files->getChild('build/collection/page/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function collection_item_page_metadata_contains_collection_name()
    {
        $config = collect(['collections' => ['collection' => []]]);
        $files = $this->setupSource([
            '_layouts' => [
                'item.blade.php' => '@section(\'content\') @endsection',
            ],
            '_collection' => [
                'page1.blade.php' => "@extends('_layouts.item')\n" . '{{ $page->getCollection() }}',
                'page2.blade.php' => "@extends('_layouts.item')\n" . '{{ $page->getCollectionName() }}',
            ],
        ]);

        $this->buildSite($files, $config, $pretty = true);

        $this->assertEquals(
            'collection',
            $this->clean($files->getChild('build/collection/page1/index.html')->getContent())
        );
        $this->assertEquals(
            'collection',
            $this->clean($files->getChild('build/collection/page2/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function collection_item_page_metadata_contains_source_path()
    {
        $config = collect(['collections' => ['collection' => []]]);
        $files = $this->setupSource([
            '_layouts' => [
                'item.blade.php' => '@section(\'content\') @endsection',
            ],
            '_collection' => [
                'page.blade.php' => "@extends('_layouts.item')\n" . '{{ $page->getSource() }}',
            ],
        ]);

        $this->buildSite($files, $config, $pretty = true);

        $this->assertEquals(
            'vfs://virtual/source/_collection',
            $this->clean($files->getChild('build/collection/page/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function collection_item_page_metadata_contains_modified_time()
    {
        $config = collect(['collections' => ['collection' => []]]);
        $files = $this->setupSource([
            '_layouts' => [
                'item.blade.php' => '@section(\'content\') @endsection',
            ],
            '_collection' => [
                'page.blade.php' => "@extends('_layouts.item')\n" . '{{ $page->getModifiedTime() }}',
            ],
        ]);

        $this->buildSite($files, $config, $pretty = true);

        $this->assertEquals(
            $files->getChild('build/collection/page/index.html')->filemtime(),
            $this->clean($files->getChild('build/collection/page/index.html')->getContent())
        );
    }
}

class MappedItem extends CollectionItem
{
    public function doubleNumber()
    {
        return $this->number * 2;
    }
}
