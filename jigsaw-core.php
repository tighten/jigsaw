<?php

use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Mni\FrontYAML\Bridge\Symfony\SymfonyYAMLParser;
use Mni\FrontYAML\Markdown\MarkdownParser;
use Mni\FrontYAML\Parser;
use Mni\FrontYAML\YAML\YAMLParser;
use TightenCo\Jigsaw\Collection\CollectionPaginator;
use TightenCo\Jigsaw\CollectionItemHandlers\BladeCollectionItemHandler;
use TightenCo\Jigsaw\CollectionItemHandlers\MarkdownCollectionItemHandler;
use TightenCo\Jigsaw\Events\EventBus;
use TightenCo\Jigsaw\File\BladeDirectivesFile;
use TightenCo\Jigsaw\File\ConfigFile;
use TightenCo\Jigsaw\File\Filesystem;
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
use TightenCo\Jigsaw\Parsers\ParsedownExtraParser;
use TightenCo\Jigsaw\PathResolvers\BasicOutputPathResolver;
use TightenCo\Jigsaw\PathResolvers\CollectionPathResolver;
use TightenCo\Jigsaw\SiteBuilder;
use TightenCo\Jigsaw\View\BladeMarkdownEngine;
use TightenCo\Jigsaw\View\MarkdownEngine;
use TightenCo\Jigsaw\View\ViewRenderer;

if (file_exists(__DIR__.'/vendor/autoload.php')) {
    require __DIR__.'/vendor/autoload.php';
} else {
    require __DIR__.'/../../autoload.php';
}

setlocale(LC_ALL, 'en_US.UTF8');

$container = new Container;

$container->instance('cwd', getcwd());

$cachePath = $container['cwd'] . '/_tmp';
$bootstrapFile = $container['cwd'] . '/bootstrap.php';

$container->instance('buildPath', [
    'source' => $container['cwd'] . '/source',
    'destination' => $container['cwd'] . '/build_{env}',
]);

$container->bind('config', function ($c) {
    return (new ConfigFile($c['cwd'] . '/config.php'))->config;
});

$container->bind('outputPathResolver', function ($c) {
    return new BasicOutputPathResolver;
});

$container->bind(YAMLParser::class, SymfonyYAMLParser::class);

$container->bind(MarkdownParser::class, ParsedownExtraParser::class);

$container->bind(Parser::class, function ($c) {
    return new Parser($c[YAMLParser::class], $c[MarkdownParser::class]);
});

$container->bind(FrontMatterParser::class, function ($c) {
    return new FrontMatterParser($c[Parser::class]);
});

$container->bind(Factory::class, function ($c) use ($cachePath) {
    $resolver = new EngineResolver;

    $bladeCompiler = new BladeCompiler(new Filesystem, $cachePath);
    $compilerEngine = new CompilerEngine($bladeCompiler, new Filesystem);

    $resolver->register('blade', function () use ($compilerEngine) {
        return $compilerEngine;
    });

    $resolver->register('php', function () {
        return new PhpEngine();
    });

    $resolver->register('markdown', function () use ($c) {
        return new MarkdownEngine($c[FrontMatterParser::class], new Filesystem, $c['buildPath']['source']);
    });

    $resolver->register('blade-markdown', function () use ($c, $compilerEngine) {
        return new BladeMarkdownEngine($compilerEngine, $c[FrontMatterParser::class]);
    });

    (new BladeDirectivesFile($c['cwd'] . '/blade.php', $bladeCompiler))->register();

    $finder = new FileViewFinder(new Filesystem, [$cachePath, $c['buildPath']['source']]);

    return new Factory($resolver, $finder, Mockery::mock(Dispatcher::class)->shouldIgnoreMissing());
});

$container->bind(ViewRenderer::class, function ($c) {
    return new ViewRenderer($c[Factory::class]);
});

$container->bind(BladeHandler::class, function ($c) {
    return new BladeHandler($c[TemporaryFilesystem::class], $c[FrontMatterParser::class], $c[ViewRenderer::class]);
});

$container->bind(TemporaryFilesystem::class, function ($c) use ($cachePath) {
    return new TemporaryFilesystem($cachePath);
});

$container->bind(MarkdownHandler::class, function ($c) {
    return new MarkdownHandler($c[TemporaryFilesystem::class], $c[FrontMatterParser::class], $c[ViewRenderer::class]);
});

$container->bind(CollectionPathResolver::class, function ($c ) {
    return new CollectionPathResolver($c['outputPathResolver'], $c[ViewRenderer::class]);
});

$container->bind(CollectionDataLoader::class, function ($c) {
    return new CollectionDataLoader(new Filesystem, $c[CollectionPathResolver::class], [
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

$container->bind(SiteBuilder::class, function ($c) use ($cachePath) {
    return new SiteBuilder(new Filesystem, $cachePath, $c['outputPathResolver'], [
        $c[CollectionItemHandler::class],
        new IgnoredHandler,
        $c[PaginatedPageHandler::class],
        $c[MarkdownHandler::class],
        $c[BladeHandler::class],
        $c[DefaultHandler::class],
    ]);
});

$container->bind(CollectionRemoteItemLoader::class, function ($c) {
    return new CollectionRemoteItemLoader(new Filesystem);
});

$container->singleton('events', function ($c) {
    return new EventBus();
});

if (file_exists($bootstrapFile)) {
    $events = $container->events;
    include $bootstrapFile;
}

$container->bind(Jigsaw::class, function ($c) {
    return new Jigsaw($c, $c[DataLoader::class], $c[CollectionRemoteItemLoader::class], $c[SiteBuilder::class]);
});
