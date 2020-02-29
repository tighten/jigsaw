<?php

namespace Tests;

use org\bovigo\vfs\vfsStream;

class SiteBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function destination_directory_is_deleted_when_building_site()
    {
        $files = vfsStream::setup('virtual', null, [
            'build' => [
                'old.blade.php' => 'Old file',
            ],
            'source' => [
                'new.blade.php' => 'New file',
            ],
        ]);

        $this->buildSite($files);

        $this->assertCount(1, $files->getChild('build')->getChildren());
    }

    /**
     * @test
     */
    public function existing_files_in_destination_directory_are_replaced_when_building_site()
    {
        $files = vfsStream::setup('virtual', null, [
            'build' => [
                'test.blade.php' => 'Old file',
            ],
            'source' => [
                'test.blade.php' => 'New file',
            ],
        ]);

        $this->buildSite($files);

        $this->assertCount(1, $files->getChild('build')->getChildren());
        $this->assertEquals('New file', $files->getChild('build')->getChild('build/test.html')->getContent());
    }

    /**
     * @test
     */
    public function page_metadata_contains_path()
    {
        $files = $this->setupSource(['nested' =>
            ['page.blade.php' => '{{ $page->getPath() }}'],
        ]);
        $this->buildSite($files, [], $pretty = true);

        $this->assertEquals(
            '/nested/page',
            $this->clean($files->getChild('build/nested/page/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function page_metadata_contains_relative_path()
    {
        $files = $this->setupSource(['nested' =>
            ['page.blade.php' => '{{ $page->getRelativePath() }}'],
        ]);
        $this->buildSite($files, [], $pretty = true);

        $this->assertEquals(
            'nested',
            $this->clean($files->getChild('build/nested/page/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function page_metadata_contains_url()
    {
        $config = collect(['baseUrl' => 'foo.com']);

        $files = $this->setupSource(['page.blade.php' => '{{ $page->getUrl() }}']);
        $this->buildSite($files, $config, $pretty = true);

        $this->assertEquals(
            'foo.com/page',
            $this->clean($files->getChild('build/page/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function page_metadata_contains_source_file_name()
    {
        $files = $this->setupSource(['page.blade.php' => '{{ $page->getFilename() }}']);
        $this->buildSite($files, [], $pretty = true);

        $this->assertEquals(
            'page',
            $this->clean($files->getChild('build/page/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function page_metadata_contains_source_file_extension()
    {
        $files = $this->setupSource(['page.blade.php' => '{{ $page->getExtension() }}']);
        $this->buildSite($files, [], $pretty = true);

        $this->assertEquals(
            'blade.php',
            $this->clean($files->getChild('build/page/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function page_metadata_contains_source_file_modified_time()
    {
        $files = $this->setupSource(['page.blade.php' => '{{ $page->getModifiedTime() }}']);
        $this->buildSite($files, [], $pretty = true);

        $this->assertEquals(
            $files->getChild('build/page/index.html')->filemtime(),
            $this->clean($files->getChild('build/page/index.html')->getContent())
        );
    }

    /**
     * @test
     */
    public function can_get_output_paths_after_building_site()
    {
        $files = $this->setupSource([
            'page1.blade.php' => 'Page 1',
            'nested' => [
                'page2.blade.php' => 'Page 2',
            ],
        ]);
        $jigsaw = $this->buildSite($files, [], $pretty = true);

        $this->assertEquals(
            [
                '/page1',
                '/nested/page2',
            ],
            $jigsaw->getOutputPaths()->toArray()
        );
    }

    /**
     * @test
     */
    public function can_get_source_file_info_after_building_site()
    {
        $files = $this->setupSource([
            'page1.blade.php' => 'Page 1',
            'nested' => [
                'page2.blade.php' => 'Page Two',
            ],
        ]);
        $jigsaw = $this->buildSite($files, [], $pretty = true);

        $source1 = $jigsaw->getSourceFileInfo()->get('/page1');
        $this->assertEquals(
            $files->getChild('build/page1/index.html')->filemtime(),
            $source1->getLastModifiedTime()
        );
        $this->assertEquals('page1.blade.php', $source1->getFilename());
        $this->assertTrue($source1->isBladeFile());
        $this->assertEquals(6, $source1->getSize());

        $source2 = $jigsaw->getSourceFileInfo()->get('/nested/page2');
        $this->assertEquals(
            $files->getChild('build/nested/page2/index.html')->filemtime(),
            $source2->getLastModifiedTime()
        );
        $this->assertEquals('page2.blade.php', $source2->getFilename());
        $this->assertTrue($source2->isBladeFile());
        $this->assertEquals(8, $source2->getSize());
    }
}
