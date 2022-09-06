<?php

use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use TightenCo\Jigsaw\Collection\CollectionPaginator;
use TightenCo\Jigsaw\CollectionItemHandlers\BladeCollectionItemHandler;
use TightenCo\Jigsaw\CollectionItemHandlers\MarkdownCollectionItemHandler;
use TightenCo\Jigsaw\Console\ConsoleOutput;
use TightenCo\Jigsaw\File\BladeDirectivesFile;
use TightenCo\Jigsaw\File\ConfigFile;
use TightenCo\Jigsaw\File\TemporaryFilesystem;
use TightenCo\Jigsaw\Handlers\BladeHandler;
use TightenCo\Jigsaw\Handlers\CollectionItemHandler;
use TightenCo\Jigsaw\Handlers\DefaultHandler;
use TightenCo\Jigsaw\Handlers\IgnoredHandler;
use TightenCo\Jigsaw\Handlers\MarkdownHandler;
use TightenCo\Jigsaw\Handlers\PaginatedPageHandler;
use TightenCo\Jigsaw\Jigsaw;
use TightenCo\Jigsaw\Loaders\CollectionDataLoader;
use TightenCo\Jigsaw\Loaders\CollectionRemoteItemLoader;
use TightenCo\Jigsaw\Loaders\DataLoader;
use TightenCo\Jigsaw\Parsers\FrontMatterParser;
use TightenCo\Jigsaw\PathResolvers\BasicOutputPathResolver;
use TightenCo\Jigsaw\PathResolvers\CollectionPathResolver;
use TightenCo\Jigsaw\SiteBuilder;
use TightenCo\Jigsaw\View\BladeCompiler;
use TightenCo\Jigsaw\View\BladeMarkdownEngine;
use TightenCo\Jigsaw\View\MarkdownEngine;
use TightenCo\Jigsaw\View\ViewRenderer;

require __DIR__ . '/vendor/autoload.php';

// TODO use __DIR__??
$container = new \TightenCo\Jigsaw\Container(getcwd());

$container->bootstrap([]);

$container->singleton('consoleOutput', function ($c) {
    return new ConsoleOutput();
});

$container->bind('outputPathResolver', function ($c) {
    return new BasicOutputPathResolver;
});

$container->bind(BladeHandler::class, function ($c) {
    return new BladeHandler($c[TemporaryFilesystem::class], $c[FrontMatterParser::class], $c[ViewRenderer::class]);
});

$container->bind(MarkdownHandler::class, function ($c) {
    return new MarkdownHandler($c[TemporaryFilesystem::class], $c[FrontMatterParser::class], $c[ViewRenderer::class]);
});

$container->bind(CollectionPathResolver::class, function ($c ) {
    return new CollectionPathResolver($c['outputPathResolver'], $c[ViewRenderer::class]);
});

$container->bind(CollectionDataLoader::class, function ($c) {
    return new CollectionDataLoader(app('files'), $c['consoleOutput'], $c[CollectionPathResolver::class], [
        $c[MarkdownCollectionItemHandler::class],
        $c[BladeCollectionItemHandler::class],
    ]);
});

$container->bind(DataLoader::class, function ($c) {
    return new DataLoader($c[CollectionDataLoader::class]);
});

$container->bind(CollectionItemHandler::class, function ($c) {
    return new CollectionItemHandler($c['config'], [
        $c[MarkdownHandler::class],
        $c[BladeHandler::class],
    ]);
});

$container->bind(CollectionPaginator::class, function ($c) {
    return new CollectionPaginator($c['outputPathResolver']);
});

$container->bind(PaginatedPageHandler::class, function ($c) {
    return new PaginatedPageHandler($c[CollectionPaginator::class], $c[FrontMatterParser::class], $c[TemporaryFilesystem::class], $c[ViewRenderer::class]);
});

$container->bind(SiteBuilder::class, function ($c) {
    return new SiteBuilder(app('files'), $c->cachePath(), $c['outputPathResolver'], $c['consoleOutput'], [
        $c[CollectionItemHandler::class],
        new IgnoredHandler,
        $c[PaginatedPageHandler::class],
        $c[MarkdownHandler::class],
        $c[BladeHandler::class],
        $c[DefaultHandler::class],
    ]);
});

$container->bind(CollectionRemoteItemLoader::class, function ($c) {
    return new CollectionRemoteItemLoader($c['config'], app('files'));
});

$container->bind(Jigsaw::class, function ($c) {
    return new Jigsaw($c, $c[DataLoader::class], $c[CollectionRemoteItemLoader::class], $c[SiteBuilder::class]);
});

if (file_exists($bootstrapFile = $container->basePath('bootstrap.php'))) {
    $events = $container->events;
    include $bootstrapFile;
}
