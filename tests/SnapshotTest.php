<?php

namespace Tests;

class SnapshotTest extends SnapshotTestCase
{
    public function test_all_files_are_built()
    {
        $this->assertEquals($this->snapshot_files, $this->build_files, 'Some files are missing or unexpected.');
    }

    public function test_all_built_files_contain_expected_content()
    {
        collect($this->build_files)->each(function ($file) {
            echo("\r\nChecking " . $file->getRelativePathname());
            $this->assertEquals(
                file_get_contents('tests/snapshots/' . $file->getRelativePathname()),
                $file->getContents(),
                'File contents do not match: ' . $file->getRelativePathname()
            );
        });

        $this->echoLine();
        echo("\r\n√ All built files pass.");
        $this->echoLine();
    }

    protected function echoLine()
    {
        echo("\r\n-----------------------");
    }
}
