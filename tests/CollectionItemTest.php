<?php

namespace Tests;

use TightenCo\Jigsaw\DataLoader;
use TightenCo\Jigsaw\SiteBuilder;
use org\bovigo\vfs\vfsStream;

class CollectionItemTest extends TestCase
{
    public function test_collection_item_contents_are_returned_when_item_is_referenced_as_a_string()
    {
        $config = collect(['collections' => ['collection' => []]]);
        $yaml_header = implode("\n", ['---', 'section: content', '---']);
        $vfs = vfsStream::setup('virtual', null, [
            'source' => [
                '_collection' => [
                    'item.md' => $yaml_header . '### Collection Item Content',
                ],
                'test_get_content.blade.php' => '<div>{!! $collection->first()->getContent() !!}</div>',
                'test_to_string.blade.php' => '<div>{!! $collection->first() !!}</div>',
            ],
        ]);

        $this->buildSite($config, $vfs);

        $this->assertEquals(
            $vfs->getChild('build/test_get_content.html')->getContent(),
            $vfs->getChild('build/test_to_string.html')->getContent()
        );
        $this->assertEquals(
            '<div><h3>Collection Item Content</h3></div>',
            $vfs->getChild('build/test_to_string.html')->getContent()
        );
    }

    protected function buildSite($config = [], $vfs)
    {
        $this->app->config = $config;
        $this->app->buildPath = [
            'source' => $vfs->url() . '/source',
            'destination' => $vfs->url() . '/build',
        ];
        $this->app->make(SiteBuilder::class)->build(
            $this->app->buildPath['source'],
            $this->app->buildPath['destination'],
            $site_data = $this->buildSiteData($config, $vfs->url() . '/source')
        );

        return $site_data;
    }

    protected function buildSiteData($config, $source_url)
    {
        return $this->app->make(DataLoader::class)->load($source_url, $config);
    }
}
