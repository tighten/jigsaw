<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use TightenCo\Jigsaw\File\Filesystem;

class TestCase extends BaseTestCase
{
    const DELETE_BUILT_FILES = true;

    public $build_files;
    public $filesystem;
    public $snapshot_files;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        exec('jigsaw build testing');
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
        $this->filesystem = new Filesystem;
        $this->build_files = $this->filesystem->allFiles('tests/build-testing');
        $this->snapshot_files = $this->filesystem->allFiles('tests/snapshots');

        parent::setUp();
    }
}
