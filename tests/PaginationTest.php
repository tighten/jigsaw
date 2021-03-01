<?php

namespace Tests;

class PaginationTest extends TestCase
{
    /**
     * @test
     */
    public function blade_template_file_can_be_paginated()
    {
        $config = collect(['collections' => ['posts' => []]]);

        $yaml_header = implode("\n", [
            '---',
            'pagination:',
            '    collection: posts',
            '    perPage: 2',
            '---',
        ]);

        $files = $this->setupSource([
            '_layouts' => [
                'post.blade.php' => '@section(\'content\') @endsection',
            ],
            '_posts' => [
                'post1.blade.php' => "@extends('_layouts.post')\n" . '1',
                'post2.blade.php' => "@extends('_layouts.post')\n" . '2',
                'post3.blade.php' => "@extends('_layouts.post')\n" . '3',
                'post4.blade.php' => "@extends('_layouts.post')\n" . '4',
                'post5.blade.php' => "@extends('_layouts.post')\n" . '5',
            ],
            'blog.blade.php' => $yaml_header . '@foreach($pagination->items as $item) {{ $item->getFilename() }}@endforeach',
        ]);

        $this->buildSite($files, $config, $pretty = true);

        $this->assertEquals(
            'post1 post2',
            $this->clean($files->getChild('build/blog/index.html')->getContent())
        );
        $this->assertEquals(
            'post3 post4',
            $this->clean($files->getChild('build/blog/2/index.html')->getContent())
        );
        $this->assertEquals(
            'post5',
            $this->clean($files->getChild('build/blog/3/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function blade_markdown_template_file_can_be_paginated()
    {
        $config = collect(['collections' => ['posts' => []]]);

        $yaml_header = implode("\n", [
            '---',
            'pagination:',
            '    collection: posts',
            '    perPage: 2',
            '---',
        ]);

        $files = $this->setupSource([
            '_layouts' => [
                'post.blade.php' => '@section(\'content\') @endsection',
            ],
            '_posts' => [
                'post1.blade.php' => "@extends('_layouts.post')\n" . '1',
                'post2.blade.php' => "@extends('_layouts.post')\n" . '2',
                'post3.blade.php' => "@extends('_layouts.post')\n" . '3',
                'post4.blade.php' => "@extends('_layouts.post')\n" . '4',
                'post5.blade.php' => "@extends('_layouts.post')\n" . '5',
            ],
            'blog.blade.md' => $yaml_header . '@foreach($pagination->items as $item) {{ $item->getFilename() }}@endforeach',
        ]);

        $this->buildSite($files, $config, $pretty = true);

        $this->assertEquals(
            'post1 post2',
            $this->clean($files->getChild('build/blog/index.html')->getContent())
        );
        $this->assertEquals(
            'post3 post4',
            $this->clean($files->getChild('build/blog/2/index.html')->getContent())
        );
        $this->assertEquals(
            'post5',
            $this->clean($files->getChild('build/blog/3/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function blade_template_file_pagination_perPage_setting_defaults_to_config_global_setting()
    {
        $config = collect(['perPage' => 2, 'collections' => ['posts' => []]]);

        $yaml_header = implode("\n", [
            '---',
            'pagination:',
            '    collection: posts',
            '---',
        ]);

        $files = $this->setupSource([
            '_layouts' => [
                'post.blade.php' => '@section(\'content\') @endsection',
            ],
            '_posts' => [
                'post1.blade.php' => "@extends('_layouts.post')\n" . '1',
                'post2.blade.php' => "@extends('_layouts.post')\n" . '2',
                'post3.blade.php' => "@extends('_layouts.post')\n" . '3',
                'post4.blade.php' => "@extends('_layouts.post')\n" . '4',
                'post5.blade.php' => "@extends('_layouts.post')\n" . '5',
            ],
            'blog.blade.php' => $yaml_header . '@foreach($pagination->items as $item) {{ $item->getFilename() }}@endforeach',
        ]);

        $this->buildSite($files, $config, $pretty = true);

        $this->assertEquals(
            'post1 post2',
            $this->clean($files->getChild('build/blog/index.html')->getContent())
        );
        $this->assertEquals(
            'post3 post4',
            $this->clean($files->getChild('build/blog/2/index.html')->getContent())
        );
        $this->assertEquals(
            'post5',
            $this->clean($files->getChild('build/blog/3/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function blade_template_file_pagination_perPage_setting_defaults_to_config_collection_setting()
    {
        $config = collect(['collections' => ['posts' => ['perPage' => 2]]]);

        $yaml_header = implode("\n", [
            '---',
            'pagination:',
            '    collection: posts',
            '---',
        ]);

        $files = $this->setupSource([
            '_layouts' => [
                'post.blade.php' => '@section(\'content\') @endsection',
            ],
            '_posts' => [
                'post1.blade.php' => "@extends('_layouts.post')\n" . '1',
                'post2.blade.php' => "@extends('_layouts.post')\n" . '2',
                'post3.blade.php' => "@extends('_layouts.post')\n" . '3',
                'post4.blade.php' => "@extends('_layouts.post')\n" . '4',
                'post5.blade.php' => "@extends('_layouts.post')\n" . '5',
            ],
            'blog.blade.php' => $yaml_header . '@foreach($pagination->items as $item) {{ $item->getFilename() }}@endforeach',
        ]);

        $this->buildSite($files, $config, $pretty = true);

        $this->assertEquals(
            'post1 post2',
            $this->clean($files->getChild('build/blog/index.html')->getContent())
        );
        $this->assertEquals(
            'post3 post4',
            $this->clean($files->getChild('build/blog/2/index.html')->getContent())
        );
        $this->assertEquals(
            'post5',
            $this->clean($files->getChild('build/blog/3/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function blade_template_file_pagination_perPage_setting_defaults_to_config_collection_setting_when_config_global_setting_exists()
    {
        $config = collect(['perPage' => 10, 'collections' => ['posts' => ['perPage' => 2]]]);

        $yaml_header = implode("\n", [
            '---',
            'pagination:',
            '    collection: posts',
            '---',
        ]);

        $files = $this->setupSource([
            '_layouts' => [
                'post.blade.php' => '@section(\'content\') @endsection',
            ],
            '_posts' => [
                'post1.blade.php' => "@extends('_layouts.post')\n" . '1',
                'post2.blade.php' => "@extends('_layouts.post')\n" . '2',
                'post3.blade.php' => "@extends('_layouts.post')\n" . '3',
                'post4.blade.php' => "@extends('_layouts.post')\n" . '4',
                'post5.blade.php' => "@extends('_layouts.post')\n" . '5',
            ],
            'blog.blade.php' => $yaml_header . '@foreach($pagination->items as $item) {{ $item->getFilename() }}@endforeach',
        ]);

        $this->buildSite($files, $config, $pretty = true);

        $this->assertEquals(
            'post1 post2',
            $this->clean($files->getChild('build/blog/index.html')->getContent())
        );
        $this->assertEquals(
            'post3 post4',
            $this->clean($files->getChild('build/blog/2/index.html')->getContent())
        );
        $this->assertEquals(
            'post5',
            $this->clean($files->getChild('build/blog/3/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function blade_template_file_pagination_perPage_setting_overrides_config_global_setting()
    {
        $config = collect(['perPage' => 10, 'collections' => ['posts' => []]]);

        $yaml_header = implode("\n", [
            '---',
            'pagination:',
            '    collection: posts',
            '    perPage: 2',
            '---',
        ]);

        $files = $this->setupSource([
            '_layouts' => [
                'post.blade.php' => '@section(\'content\') @endsection',
            ],
            '_posts' => [
                'post1.blade.php' => "@extends('_layouts.post')\n" . '1',
                'post2.blade.php' => "@extends('_layouts.post')\n" . '2',
                'post3.blade.php' => "@extends('_layouts.post')\n" . '3',
                'post4.blade.php' => "@extends('_layouts.post')\n" . '4',
                'post5.blade.php' => "@extends('_layouts.post')\n" . '5',
            ],
            'blog.blade.php' => $yaml_header . '@foreach($pagination->items as $item) {{ $item->getFilename() }}@endforeach',
        ]);

        $this->buildSite($files, $config, $pretty = true);

        $this->assertEquals(
            'post1 post2',
            $this->clean($files->getChild('build/blog/index.html')->getContent())
        );
        $this->assertEquals(
            'post3 post4',
            $this->clean($files->getChild('build/blog/2/index.html')->getContent())
        );
        $this->assertEquals(
            'post5',
            $this->clean($files->getChild('build/blog/3/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function blade_template_file_pagination_perPage_setting_overrides_config_collection_setting()
    {
        $config = collect(['collections' => ['posts' => ['perPage' => 10]]]);

        $yaml_header = implode("\n", [
            '---',
            'pagination:',
            '    collection: posts',
            '    perPage: 2',
            '---',
        ]);

        $files = $this->setupSource([
            '_layouts' => [
                'post.blade.php' => '@section(\'content\') @endsection',
            ],
            '_posts' => [
                'post1.blade.php' => "@extends('_layouts.post')\n" . '1',
                'post2.blade.php' => "@extends('_layouts.post')\n" . '2',
                'post3.blade.php' => "@extends('_layouts.post')\n" . '3',
                'post4.blade.php' => "@extends('_layouts.post')\n" . '4',
                'post5.blade.php' => "@extends('_layouts.post')\n" . '5',
            ],
            'blog.blade.php' => $yaml_header . '@foreach($pagination->items as $item) {{ $item->getFilename() }}@endforeach',
        ]);

        $this->buildSite($files, $config, $pretty = true);

        $this->assertEquals(
            'post1 post2',
            $this->clean($files->getChild('build/blog/index.html')->getContent())
        );
        $this->assertEquals(
            'post3 post4',
            $this->clean($files->getChild('build/blog/2/index.html')->getContent())
        );
        $this->assertEquals(
            'post5',
            $this->clean($files->getChild('build/blog/3/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function blade_template_file_pagination_perPage_setting_overrides_config_collection_and_global_settings()
    {
        $config = collect(['perPage' => 20, 'collections' => ['posts' => ['perPage' => 10]]]);

        $yaml_header = implode("\n", [
            '---',
            'pagination:',
            '    collection: posts',
            '    perPage: 2',
            '---',
        ]);

        $files = $this->setupSource([
            '_layouts' => [
                'post.blade.php' => '@section(\'content\') @endsection',
            ],
            '_posts' => [
                'post1.blade.php' => "@extends('_layouts.post')\n" . '1',
                'post2.blade.php' => "@extends('_layouts.post')\n" . '2',
                'post3.blade.php' => "@extends('_layouts.post')\n" . '3',
                'post4.blade.php' => "@extends('_layouts.post')\n" . '4',
                'post5.blade.php' => "@extends('_layouts.post')\n" . '5',
            ],
            'blog.blade.php' => $yaml_header . '@foreach($pagination->items as $item) {{ $item->getFilename() }}@endforeach',
        ]);

        $this->buildSite($files, $config, $pretty = true);

        $this->assertEquals(
            'post1 post2',
            $this->clean($files->getChild('build/blog/index.html')->getContent())
        );
        $this->assertEquals(
            'post3 post4',
            $this->clean($files->getChild('build/blog/2/index.html')->getContent())
        );
        $this->assertEquals(
            'post5',
            $this->clean($files->getChild('build/blog/3/index.html')->getContent())
        );
    }
}
