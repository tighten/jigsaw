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
}
