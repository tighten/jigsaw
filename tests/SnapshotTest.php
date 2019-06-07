<?php

namespace Tests;

class SnapshotTest extends SnapshotTestCase
{
    /**
     * @test
     */
    public function all_files_are_built()
    {
        $this->assertEquals($this->snapshot_files, $this->build_files, 'Some files are missing or unexpected.');
    }

    /**
     * @test
     */
    public function dot_files_are_built()
    {
        $this->assertFileExists('tests/build-testing/.dotfile-test', 'dotfile was not built');
    }

    /**
     * @test
     */
    public function ds_store_files_are_not_built()
    {
        $this->assertFileNotExists('tests/build-testing/.DS_Store', 'DS_Store was built');
    }


    /**
     * @test
     */
    public function all_built_files_contain_expected_content()
    {
        collect($this->build_files)->each(function ($file) {
            echo "\r\nChecking " . $file->getRelativePathname();
            $this->assertEquals(
                file_get_contents('tests/snapshots/' . $file->getRelativePathname()),
                $file->getContents(),
                'File contents do not match: ' . $file->getRelativePathname()
            );
        });

        $this->echoLine();
        echo "\r\n√ All built files pass.";
        $this->echoLine();
    }

    protected function echoLine()
    {
        echo "\r\n-----------------------";
    }
}
