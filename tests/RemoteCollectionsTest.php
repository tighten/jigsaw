<?php

namespace Tests;

use TightenCo\Jigsaw\DataLoader;
use TightenCo\Jigsaw\File\Filesystem;
use org\bovigo\vfs\vfsStream;

class RemoteCollectionsTest extends TestCase
{
    public function test_collection_does_not_require_matching_directory()
    {
        $config = collect(['collections' => ['collection_without_directory' => []]]);
        $vfs = vfsStream::setup('source', null, []);
        $site = $this->buildSite($config, $vfs);

        $this->assertCount(0, $site->collection_without_directory);
    }

    public function test_it_creates_collection_items_from_files_in_a_collection_directory()
    {
        $config = collect(['collections' => ['collection' => []]]);
        $vfs = vfsStream::setup('source', null, [
            '_collection' => [
                'file_1.md' => 'Test markdown file #1',
                'file_2.md' => 'Test markdown file #2',
            ]
        ]);
        $site = $this->buildSite($config, $vfs);

        $this->assertCount(2, $site->collection);
        $this->assertEquals('<p>Test markdown file #1</p>', $site->collection->file_1->getContent());
        $this->assertEquals('<p>Test markdown file #2</p>', $site->collection->file_2->getContent());
    }

    public function test_it_creates_collection_items_from_json_files_in_a_collection_directory()
    {
        $config = collect(['collections' => ['collection' => []]]);
        $vfs = vfsStream::setup('source', null, [
            '_collection' => [
                'file.json' => json_encode([
                    'title' => 'JSON Collection Item',
                    'content' => 'The content of the JSON file',
                ]),
            ]
        ]);
        $site = $this->buildSite($config, $vfs);

        $this->assertCount(1, $site->collection);
        $this->assertEquals('JSON Collection Item', $site->collection->file->title);
        $this->assertEquals('The content of the JSON file', $site->collection->file->getContent());
    }

    protected function buildSite($config, $vfs)
    {
        return $this->app->make(DataLoader::class)->load($vfs->url(), $config);
    }
}
