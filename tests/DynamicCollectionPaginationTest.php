<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Test;
use TightenCo\Jigsaw\Jigsaw;

class DynamicCollectionPaginationTest extends TestCase
{
    #[Test]
    public function can_paginate_a_dynamically_registered_collection()
    {
        $files = $this->setupSource([
            '_layouts' => [
                'tag.blade.php' => '@foreach($pagination->items as $post){{ $post->getFilename() }}@endforeach',
            ],
            '_posts' => [
                'post1.blade.php' => "---\ntags:\n  - php\n---\n",
                'post2.blade.php' => "---\ntags:\n  - php\n---\n",
                'post3.blade.php' => "---\ntags:\n  - php\n---\n",
                'post4.blade.php' => "---\ntags:\n  - laravel\n---\n",
                'post5.blade.php' => "---\ntags:\n  - laravel\n---\n",
            ],
        ]);

        $this->app['events']->afterCollections(function (Jigsaw $jigsaw) {
            $posts = $jigsaw->getCollection('posts');

            $jigsaw->setConfig('tag_php', $posts->filter(fn ($post) => in_array('php', $post->tags ?? [])));
            $jigsaw->paginateCollection(
                path: 'tags/php',
                collection: 'tag_php',
                template: '_layouts.tag',
                perPage: 2,
            );

            $jigsaw->setConfig('tag_laravel', $posts->filter(fn ($post) => in_array('laravel', $post->tags ?? [])));
            $jigsaw->paginateCollection(
                path: 'tags/laravel',
                collection: 'tag_laravel',
                template: '_layouts.tag',
                perPage: 2,
            );
        });

        $this->buildSite($files, ['collections' => ['posts' => []]], $pretty = true);

        $phpPage1 = $this->clean($files->getChild('build/tags/php/index.html')->getContent());
        $phpPage2 = $this->clean($files->getChild('build/tags/php/2/index.html')->getContent());
        $laravelPage1 = $this->clean($files->getChild('build/tags/laravel/index.html')->getContent());

        $this->assertStringContainsString('post1', $phpPage1);
        $this->assertStringNotContainsString('post1', $laravelPage1);
        $this->assertStringContainsString('post4', $laravelPage1);
        $this->assertStringNotContainsString('post4', $phpPage1);
        $this->assertStringNotContainsString('post4', $phpPage2);
        $this->assertFileMissing($this->tmpPath('build/tags/php/3/index.html'));
        $this->assertFileMissing($this->tmpPath('build/tags/laravel/2/index.html'));
    }

    #[Test]
    public function paginate_collection_generates_correct_page_count()
    {
        $files = $this->setupSource([
            '_layouts' => [
                'items.blade.php' => '{{ $pagination->totalPages }}',
            ],
            '_items' => [
                'item1.blade.php' => '',
                'item2.blade.php' => '',
                'item3.blade.php' => '',
                'item4.blade.php' => '',
                'item5.blade.php' => '',
            ],
        ]);

        $this->app['events']->afterCollections(function (Jigsaw $jigsaw) {
            $jigsaw->paginateCollection(
                path: 'list',
                collection: 'items',
                template: '_layouts.items',
                perPage: 2,
            );
        });

        $this->buildSite($files, ['collections' => ['items' => []]], $pretty = true);

        $this->assertEquals('3', $this->clean($files->getChild('build/list/index.html')->getContent()));
        $this->assertEquals('3', $this->clean($files->getChild('build/list/2/index.html')->getContent()));
        $this->assertEquals('3', $this->clean($files->getChild('build/list/3/index.html')->getContent()));
        $this->assertFileMissing($this->tmpPath('build/list/4/index.html'));
    }

    #[Test]
    public function paginate_collection_exposes_pagination_navigation_links()
    {
        $files = $this->setupSource([
            '_layouts' => [
                'items.blade.php' => '{{ $pagination->previous }}|{{ $pagination->next }}',
            ],
            '_items' => [
                'item1.blade.php' => '',
                'item2.blade.php' => '',
                'item3.blade.php' => '',
            ],
        ]);

        $this->app['events']->afterCollections(function (Jigsaw $jigsaw) {
            $jigsaw->paginateCollection(
                path: 'list',
                collection: 'items',
                template: '_layouts.items',
                perPage: 1,
            );
        });

        $this->buildSite($files, ['collections' => ['items' => []]], $pretty = true);

        $this->assertEquals('|/list/2', $this->clean($files->getChild('build/list/index.html')->getContent()));
        $this->assertEquals('/list|/list/3', $this->clean($files->getChild('build/list/2/index.html')->getContent()));
        $this->assertEquals('/list/2|', $this->clean($files->getChild('build/list/3/index.html')->getContent()));
    }

    #[Test]
    public function paginate_collection_passes_extra_variables_to_template()
    {
        $files = $this->setupSource([
            '_layouts' => [
                'items.blade.php' => '{{ $page->label }}',
            ],
            '_items' => [
                'item1.blade.php' => '',
            ],
        ]);

        $this->app['events']->afterCollections(function (Jigsaw $jigsaw) {
            $jigsaw->paginateCollection(
                path: 'list',
                collection: 'items',
                template: '_layouts.items',
                perPage: 10,
                variables: ['label' => 'Hello from variables'],
            );
        });

        $this->buildSite($files, ['collections' => ['items' => []]], $pretty = true);

        $this->assertEquals('Hello from variables', $this->clean($files->getChild('build/list/index.html')->getContent()));
    }

    #[Test]
    public function paginate_collection_can_be_called_multiple_times_for_multiple_collections()
    {
        $files = $this->setupSource([
            '_layouts' => [
                'items.blade.php' => '{{ $pagination->totalPages }}',
            ],
            '_items' => [
                'item1.blade.php' => '',
                'item2.blade.php' => '',
                'item3.blade.php' => '',
            ],
        ]);

        $this->app['events']->afterCollections(function (Jigsaw $jigsaw) {
            $jigsaw->paginateCollection(
                path: 'list-a',
                collection: 'items',
                template: '_layouts.items',
                perPage: 1,
            );
            $jigsaw->paginateCollection(
                path: 'list-b',
                collection: 'items',
                template: '_layouts.items',
                perPage: 2,
            );
        });

        $this->buildSite($files, ['collections' => ['items' => []]], $pretty = true);

        $this->assertEquals('3', $this->clean($files->getChild('build/list-a/index.html')->getContent()));
        $this->assertEquals('3', $this->clean($files->getChild('build/list-a/3/index.html')->getContent()));
        $this->assertFileMissing($this->tmpPath('build/list-a/4/index.html'));

        $this->assertEquals('2', $this->clean($files->getChild('build/list-b/index.html')->getContent()));
        $this->assertEquals('2', $this->clean($files->getChild('build/list-b/2/index.html')->getContent()));
        $this->assertFileMissing($this->tmpPath('build/list-b/3/index.html'));
    }
}
