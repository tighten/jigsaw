<?php

namespace Tests;

use TightenCo\Jigsaw\Jigsaw;
use TightenCo\Jigsaw\SiteBuilder;
use org\bovigo\vfs\vfsStream;

class CollectionItemTest extends TestCase
{
    public function test_collection_item_contents_are_returned_when_item_is_referenced_as_a_string()
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
}
