<?php

namespace Tests;

use Illuminate\Support\Str;
use Illuminate\View\Component;
use Mockery;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase as BaseTestCase;
use TightenCo\Jigsaw\Container;
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
    public $sourcePath = __DIR__ . '/snapshots/default/source';
    public $destinationPath = __DIR__ . '/snapshots/default/build_local';

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Container;
        /** @internal The '__testing' binding is for Jigsaw development only and may be removed. */
        $this->app['__testing'] = true;
        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \TightenCo\Jigsaw\Exceptions\Handler::class,
        );
        $this->app->bootstrapWith([
            \TightenCo\Jigsaw\Bootstrap\HandleExceptions::class,
        ]);

        $this->app->buildPath = [
            'source' => $this->sourcePath,
            'views' => $this->sourcePath,
            'destination' => $this->destinationPath,
        ];
        $this->filesystem = new Filesystem();
        $this->tempPath = $this->app->cachePath();
        $this->prepareTempDirectory();
    }

    protected function tearDown(): void
    {
        $this->cleanupTempDirectory();
        Mockery::close();

        if (method_exists(Component::class, 'flushCache')) {
            Component::flushCache();
            Component::forgetComponentsResolver();
            Component::forgetFactory();
        }

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

    public function buildSite($vfs, $config = [], $pretty = false, $viewPath = '/source')
    {
        $this->app->consoleOutput->setup($verbosity = -1);
        $this->app->config = collect($this->app->config)->merge($config);
        $this->app->buildPath = [
            'source' => $vfs->url() . '/source',
            'views' => $vfs->url() . $viewPath,
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
        return str_replace("\n", '', $output);
    }

    protected function fixDirectorySlashes(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}
