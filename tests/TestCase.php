<?php

namespace Tests;

use Illuminate\Support\Str;
use Mockery;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase as BaseTestCase;
use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\File\InputFile;
use TightenCo\Jigsaw\Jigsaw;
use TightenCo\Jigsaw\Loaders\DataLoader;
use TightenCo\Jigsaw\PathResolvers\PrettyOutputPathResolver;

class TestCase extends BaseTestCase
{
    public $app;
    public $filesystem;
    public $tempPath;
    public $sourcePath = __DIR__ . '/source';
    public $destinationPath = __DIR__ . '/build_testing';

    public function setUp()
    {
        parent::setUp();
        require 'jigsaw-core.php';
        $this->app = $container;
        $this->app->buildPath = [
            'source' => $this->sourcePath,
            'destination' => $this->destinationPath,
        ];
        $this->filesystem = new Filesystem();
        $this->tempPath = $cachePath;
        $this->prepareTempDirectory();
    }

    public function tearDown()
    {
        $this->cleanupTempDirectory();
        Mockery::close();
        parent::tearDown();
    }

    public function prepareTempDirectory()
    {
        if (! $this->filesystem->isDirectory($this->tempPath)) {
            $this->filesystem->makeDirectory($this->tempPath, 0755, true);
        }
    }

    public function cleanupTempDirectory()
    {
        $this->filesystem->deleteDirectory($this->tempPath);
    }

    public function getInputFile($filename)
    {
        $sourceFile = $this->filesystem->getFile(Str::finish($this->sourcePath, '/') . pathinfo($filename)['dirname'], basename($filename));

        return new InputFile($sourceFile, $this->sourcePath);
    }

    public function setupSource($source = [])
    {
        return vfsStream::setup('virtual', null, ['source' => $source]);
    }

    protected function buildSiteData($vfs, $config = [])
    {
        $this->app->consoleOutput->setup($verbosity = -1);
        $loader = $this->app->make(DataLoader::class);
        $siteData = $loader->loadSiteData($config);
        $collectionData = $loader->loadCollectionData($siteData, $vfs->url() . '/source');

        return $siteData->addCollectionData($collectionData);
    }

    public function buildSite($vfs, $config = [], $pretty = false)
    {
        $this->app->consoleOutput->setup($verbosity = -1);
        $this->app->config = collect($this->app->config)->merge($config);
        $this->app->buildPath = [
            'source' => $vfs->url() . '/source',
            'destination' => $vfs->url() . '/build',
        ];

        if ($pretty) {
            $this->app->instance('outputPathResolver', new PrettyOutputPathResolver());
        }

        return $this->app
            ->make(Jigsaw::class)
            ->build('test');
    }

    public function clean($output)
    {
        return str_replace("\n", "", $output);
    }

    protected function fixDirSlashes(string $path): string
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
        {
            return str_replace('/', '\\', $path);
        }

        return $path;
    }
}
