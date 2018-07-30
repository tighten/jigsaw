<?php

namespace Tests;

use TightenCo\Jigsaw\Jigsaw;
use org\bovigo\vfs\vfsStream;

class EventsTest extends TestCase
{
    public function test_user_can_add_event_listeners_as_closures()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$a) {
            $a = $jigsaw->getConfig('a');
        });
        $this->app['events']->afterCollections(function ($jigsaw) use (&$b) {
            $b = $jigsaw->getConfig('b');
        });
        $this->app['events']->afterBuild(function ($jigsaw) use (&$c) {
            $c = $jigsaw->getConfig('c');
        });
        $this->buildSite($this->setupSource(), [
            'a' => 123,
            'b' => 456,
            'c' => 789,
        ]);

        $this->assertEquals(123, $a);
        $this->assertEquals(456, $b);
        $this->assertEquals(789, $c);
    }

    public function test_user_can_add_event_listeners_as_classes()
    {
        $this->app['events']->beforeBuild(TestListener::class);
        $jigsaw = $this->buildSite($this->setupSource(), ['variable_a' => 'set in config.php']);

        $this->assertEquals('set in TestListener', $jigsaw->getConfig('variable_a'));
    }

    public function test_multiple_event_listeners_are_fired_in_the_order_they_were_defined()
    {
        $this->app['events']->beforeBuild([TestListener::class, TestListenerTwo::class]);
        $jigsaw = $this->buildSite($this->setupSource(), ['variable_a' => 'set in config.php']);

        $this->assertEquals('set in SecondTestListener', $jigsaw->getConfig('variable_a'));
        $this->assertEquals('set in TestListener', $jigsaw->getConfig('variable_b'));
    }

    public function test_listeners_for_undefined_events_are_ignored()
    {
        $this->app['events']->someUndefinedEvent(function ($jigsaw) use (&$result) {
            $result = 'value if fired';
        });
        $jigsaw = $this->buildSite($this->setupSource());

        $this->assertNull($result);
    }

    public function test_user_can_retrieve_environment_in_event_listener()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $result = $jigsaw->getEnvironment();
        });
        $this->buildSite($this->setupSource());

        $this->assertEquals('test', $result);
    }

    public function test_user_can_retrieve_all_config_variables_in_event_listener()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $result = $jigsaw->getConfig();
        });
        $this->buildSite($this->setupSource(), ['test_variable' => 'value']);

        $this->assertEquals('value', $result->test_variable);
    }

    public function test_user_can_retrieve_specific_config_variable_in_event_listener()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $result = $jigsaw->getConfig('test_variable');
        });
        $this->buildSite($this->setupSource(), ['test_variable' => 'value']);

        $this->assertEquals('value', $result);
    }

    public function test_user_can_add_a_new_config_variable_in_event_listener()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $jigsaw->setConfig('new_variable', 'value');
            $result = $jigsaw;
        });

        $this->buildSite($this->setupSource());

        $this->assertEquals('value', $result->getSiteData()->new_variable);
        $this->assertEquals('value', $result->getConfig('new_variable'));
    }

    public function test_user_can_update_an_existing_config_variable_in_event_listener()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $jigsaw->setConfig('test_variable', 'new value');
            $result = $jigsaw;
        });
        $this->buildSite($this->setupSource(), ['test_variable' => 'original value']);

        $this->assertEquals('new value', $result->getSiteData()->test_variable);
        $this->assertEquals('new value', $result->getConfig('test_variable'));
    }

    public function test_user_can_get_a_nested_config_variable_with_dot_notation_in_event_listener()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $result = $jigsaw->getConfig('test_variable.some_key');
        });
        $this->buildSite($this->setupSource(), [
            'test_variable' => [
                'some_key' => 'value',
            ],
        ]);

        $this->assertEquals('value', $result);
    }

    public function test_user_can_add_a_nested_config_variable_with_dot_notation_in_event_listener()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $jigsaw->setConfig('test_variable.new_key', 'new value');
            $result = $jigsaw;
        });
        $this->buildSite($this->setupSource(), [
            'test_variable' => [
                'existing_key' => 'original value',
            ],
        ]);

        $this->assertEquals('new value', $result->getSiteData()->test_variable['new_key']);
        $this->assertEquals('new value', $result->getConfig('test_variable')['new_key']);
        $this->assertEquals('original value', $result->getConfig('test_variable')['existing_key']);
    }

    public function test_user_can_update_an_existing_nested_config_variable_with_dot_notation_in_event_listener()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $jigsaw->setConfig('test_variable.nested', 'new value');
            $result = $jigsaw;
        });
        $this->buildSite($this->setupSource(), [
            'test_variable' => [
                'nested' => 'original value'
            ],
        ]);

        $this->assertEquals('new value', $result->getSiteData()->test_variable['nested']);
        $this->assertEquals('new value', $result->getConfig('test_variable')['nested']);
    }

    public function test_collection_items_created_in_before_build_event_listener_are_output_to_filesystem()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $jigsaw->setConfig('collections.posts', [
                'extends' => '_layouts.master',
                'items' => [
                    [
                        'content' => 'Content for post #1',
                        'filename' => 'post_1',
                    ],
                ],
            ]);
        });
        $files = $this->setupSource([
            '_layouts' => [
                'master.blade.php' => "<div>@yield('content')</div>",
            ],
        ]);
        $config = collect(['collections' => ['posts' => []]]);

        $this->buildSite($files, $config);

        $this->assertCount(1, $files->getChild('build/posts')->getChildren());
        $this->assertEquals(
            '<div><p>Content for post #1</p></div>',
            $files->getChild('build/posts/post-1.html')->getContent()
        );
    }

    public function test_collection_items_added_in_before_build_event_listener_are_output_to_filesystem()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $jigsaw->setConfig('collections.posts', [
                'extends' => '_layouts.master',
                'items' => [
                    [
                        'content' => 'Content for post #2',
                        'filename' => 'post_2',
                    ],
                ],
            ]);
        });
        $yaml_header = implode("\n", ['---', 'extends: _layouts.master', 'section: content', '---']);
        $files = $this->setupSource([
            '_layouts' => [
                'master.blade.php' => "<div>@yield('content')</div>",
            ],
            '_posts' => [
                'post_1.md' => $yaml_header . 'Content for post #1',
            ],
        ]);
        $config = collect(['collections' => ['posts' => []]]);

        $this->buildSite($files, $config);

        $this->assertCount(2, $files->getChild('build/posts')->getChildren());
        $this->assertEquals(
            '<div><p>Content for post #1</p></div>',
            $files->getChild('build/posts/post-1.html')->getContent()
        );
        $this->assertEquals(
            '<div><p>Content for post #2</p></div>',
            $files->getChild('build/posts/post-2.html')->getContent()
        );
    }

    public function test_user_can_retrieve_a_collection_of_collection_names_in_event_listener()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $result = $jigsaw->getCollections();
        });
        $this->buildSite($this->setupSource(), [
            'collections' => [
                'posts' => [],
                'people' => [],
            ],
        ]);

        $this->assertEquals(['posts', 'people'], $result->all());
    }

    public function test_user_can_retrieve_a_collection_of_collection_items_in_event_listener()
    {
        $this->app['events']->afterCollections(function ($jigsaw) use (&$result) {
            $result = $jigsaw->getCollection('posts');
        });
        $files = $this->setupSource([
            '_posts' => [
                'post_1.md' => 'Content for post #1',
            ],
        ]);
        $config = collect([
            'collections' => [
                'posts' => [
                    'items' => [
                        [
                            'title' => 'Title for post #2',
                            'content' => 'Content for post #2',
                            'filename' => 'post_2',
                        ],
                    ],
                ],
            ],
        ]);
        $this->buildSite($files, $config);

        $this->assertEquals('<p>Content for post #1</p>', $result->post_1->getContent());
        $this->assertEquals('<p>Content for post #2</p>', $result->post_2->getContent());
        $this->assertEquals('Title for post #2', $result->post_2->title);
    }

    public function test_collection_items_cannot_be_retrieved_during_before_build_event()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $result = $jigsaw->getCollection('posts');
        });
        $files = $this->setupSource([
            '_posts' => [
                'post_1.md' => 'Content for post #1',
            ],
        ]);
        $config = [
            'collections' => [
                'posts' => [],
            ],
        ];
        $this->buildSite($files, $config);

        $this->assertNull($result);
    }

    public function test_user_can_retrieve_source_path_in_event_listener()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $result = $jigsaw->getSourcePath();
        });
        $this->buildSite($source = $this->setupSource());

        $this->assertEquals($source->url() . '/source', $result);
    }

    public function test_user_can_change_source_path_in_event_listener()
    {
        $source = vfsStream::setup('virtual', null, [
            'source' => [
                'file_in_original_source.html' => 'original',
            ],
            'new_source' => [
                'file_in_new_source.html' => 'new',
            ],
        ]);
        $this->app['events']->beforeBuild(function ($jigsaw) use ($source, &$original, &$result) {
            $original = $jigsaw->getSourcePath();
            $jigsaw->setSourcePath($source->url() . '/new_source');
            $result = $jigsaw->getSourcePath();
        });
        $this->buildSite($source);

        $this->assertEquals($source->url() . '/source', $original);
        $this->assertEquals($source->url() . '/new_source', $result);
        $this->assertNull($source->getChild('build/file_in_original_source.html'));
        $this->assertEquals('new', $source->getChild('build/file_in_new_source.html')->getContent());
    }

    public function test_user_can_retrieve_destination_path_in_event_listener()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $result = $jigsaw->getDestinationPath();
        });
        $this->buildSite($source = $this->setupSource());

        $this->assertEquals($source->url() . '/build', $result);
    }

    public function test_user_can_change_destination_path_in_event_listener()
    {
        $source = $this->setupSource(['file.html' => 'test']);

        $this->app['events']->beforeBuild(function ($jigsaw) use ($source, &$original, &$result) {
            $original = $jigsaw->getDestinationPath();
            $jigsaw->setDestinationPath($source->url() . '/new_build');
            $result = $jigsaw->getDestinationPath();
        });
        $this->buildSite($source);

        $this->assertEquals($source->url() . '/build', $original);
        $this->assertEquals($source->url() . '/new_build', $result);
        $this->assertNull($source->getChild('build/file.html'));
        $this->assertEquals('test', $source->getChild('new_build/file.html')->getContent());
    }

    public function test_user_can_read_the_contents_of_source_file_in_event_listener()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $result = $jigsaw->readSourceFile('file.md');
        });
        $this->buildSite($this->setupSource(['file.md' => '## test']));

        $this->assertEquals('## test', $result);
    }

    public function test_user_can_write_a_new_source_file_in_event_listener()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $jigsaw->writeSourceFile('file.md', '## test');
        });
        $this->buildSite($source = $this->setupSource());

        $this->assertEquals('## test', $source->getChild('source/file.md')->getContent());
    }

    public function test_user_can_write_a_new_source_file_in_a_new_directory_in_event_listener()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $result = $jigsaw->writeSourceFile('new_directory/file.md', '## test');
        });
        $this->buildSite($source = $this->setupSource());

        $this->assertEquals('## test', $source->getChild('source/new_directory/file.md')->getContent());
    }

    public function test_user_can_update_an_existing_source_file_in_event_listener()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$result) {
            $jigsaw->writeSourceFile('file.md', '## updated');
        });
        $this->buildSite($source = $this->setupSource(['file.md' => '## original']));

        $this->assertEquals('## updated', $source->getChild('source/file.md')->getContent());
    }

    public function test_user_can_read_the_contents_of_an_output_file_in_after_build_event_listener()
    {
        $this->app['events']->afterBuild(function ($jigsaw) use (&$result) {
            $result = $jigsaw->readOutputFile('test/file.html');
        });
        $this->buildSite($source = $this->setupSource([
            'test' => [
                'file.blade.php' => '<h1>test</h1>'
            ],
        ]));

        $this->assertEquals('<h1>test</h1>', $result);
    }

    public function test_user_can_write_a_new_output_file_in_after_build_event_listener()
    {
        $this->app['events']->afterBuild(function ($jigsaw) use (&$result) {
            $result = $jigsaw->writeOutputFile('file.html', 'test');
        });
        $this->buildSite($source = $this->setupSource());

        $this->assertEquals('test', $source->getChild('build/file.html')->getContent());
    }

    public function test_user_can_update_an_existing_output_file_in_after_build_event_listener()
    {
        $this->app['events']->afterBuild(function ($jigsaw) use (&$original, &$result) {
            $original = $jigsaw->readOutputFile('test/file.html');
            $result = $jigsaw->writeOutputFile('test/file.html', '<h1>revised test</h1>');
        });
        $this->buildSite($source = $this->setupSource([
            'test' => [
                'file.blade.php' => '<h1>test</h1>'
            ],
        ]));

        $this->assertEquals('<h1>test</h1>', $original);
        $this->assertEquals('<h1>revised test</h1>', $source->getChild('build/test/file.html')->getContent());
    }

    public function test_user_can_write_a_new_output_file_in_a_new_directory_in_after_build_event_listener()
    {
        $this->app['events']->afterBuild(function ($jigsaw) use (&$result) {
            $result = $jigsaw->writeOutputFile('new_directory/file.html', 'test');
        });
        $this->buildSite($source = $this->setupSource());

        $this->assertEquals('test', $source->getChild('build/new_directory/file.html')->getContent());
    }
}

class TestListener
{
    public function handle($jigsaw)
    {
        $jigsaw->setConfig('variable_a', 'set in TestListener');
        $jigsaw->setConfig('variable_b', 'set in TestListener');
    }
}

class TestListenerTwo
{
    public function handle($jigsaw)
    {
        $jigsaw->setConfig('variable_a', 'set in SecondTestListener');
    }
}
