<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\File\InputFile;

class TestCase extends BaseTestCase
{
    public $app;
    public $filesystem;
    public $tempPath;
    protected $sourcePath = __DIR__ . '/source';
    protected $destinationPath = __DIR__ . '/build_testing';

    public function setUp()
    {
        parent::setUp();
        require('jigsaw-core.php');
        $this->app = $container;
        $this->app->buildPath = [
            'source' => $this->sourcePath,
            'destination' => $this->destinationPath,
        ];

        $this->filesystem = new Filesystem;
        $this->tempPath = $cachePath;
        $this->prepareTempDirectory();
    }

    public function tearDown()
    {
        $this->cleanupTempDirectory();
        parent::tearDown();
    }

    protected function prepareTempDirectory()
    {
        if (! $this->filesystem->isDirectory($this->tempPath)) {
            $this->filesystem->makeDirectory($this->tempPath, 0755, true);
        }
    }

    protected function cleanupTempDirectory()
    {
        $this->filesystem->deleteDirectory($this->tempPath);
    }

    public function getInputFile($filename)
    {
        $sourceFile = $this->filesystem->getFile(str_finish($this->sourcePath, '/') . pathinfo($filename)['dirname'], basename($filename));

        return new InputFile($sourceFile, $this->sourcePath);
    }
}
