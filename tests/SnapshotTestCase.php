<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use TightenCo\Jigsaw\File\Filesystem;

class SnapshotTestCase extends BaseTestCase
{
    const DELETE_BUILT_FILES = true;

    public $build_files;
    public $filesystem;
    public $snapshot_files;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        echo shell_exec('./jigsaw build testing');
    }

    public static function tearDownAfterClass()
    {
        if (self::DELETE_BUILT_FILES) {
            (new Filesystem)->deleteDirectory('tests/build-testing');
        }

        parent::tearDownAfterClass();
    }

    public function setUp()
    {
        try {
            $this->filesystem = new Filesystem;
            $this->build_files = $this->filesystem->allFiles('tests/build-testing');
        } catch (\Exception $e) {
            die("Error: Jigsaw test site was not built.\r\n");
        }

        try {
            $this->snapshot_files = $this->filesystem->allFiles('tests/snapshots');
        } catch (\Exception $e) {
            die("Error: Snapshot files are missing.\r\n");
        }

        parent::setUp();
    }
}
