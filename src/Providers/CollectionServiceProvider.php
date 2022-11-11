<?php

namespace TightenCo\Jigsaw\Providers;

use TightenCo\Jigsaw\Collection\CollectionPaginator;
use TightenCo\Jigsaw\CollectionItemHandlers\BladeCollectionItemHandler;
use TightenCo\Jigsaw\CollectionItemHandlers\MarkdownCollectionItemHandler;
use TightenCo\Jigsaw\Container;
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
use TightenCo\Jigsaw\Support\ServiceProvider;
use TightenCo\Jigsaw\View\ViewRenderer;

class CollectionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('outputPathResolver', fn () => new BasicOutputPathResolver);

        $this->registerHandlers();
        $this->registerPathResolver();
        $this->registerLoaders();
        $this->registerPaginator();
        $this->registerSiteBuilder();

        $this->app->bind(Jigsaw::class, function (Container $app) {
            return new Jigsaw($app, $app[DataLoader::class], $app[CollectionRemoteItemLoader::class], $app[SiteBuilder::class]);
        });
    }

    private function registerHandlers(): void
    {
        $this->app->bind(BladeHandler::class, function (Container $app) {
            return new BladeHandler($app[TemporaryFilesystem::class], $app[FrontMatterParser::class], $app[ViewRenderer::class]);
        });

        $this->app->bind(MarkdownHandler::class, function (Container $app) {
            return new MarkdownHandler($app[TemporaryFilesystem::class], $app[FrontMatterParser::class], $app[ViewRenderer::class]);
        });

        $this->app->bind(CollectionItemHandler::class, function (Container $app) {
            return new CollectionItemHandler($app['config'], [
                $app[MarkdownHandler::class],
                $app[BladeHandler::class],
            ]);
        });
    }

    private function registerPathResolver(): void
    {
        $this->app->bind(CollectionPathResolver::class, function (Container $app) {
            return new CollectionPathResolver($app['outputPathResolver'], $app[ViewRenderer::class]);
        });
    }

    private function registerLoaders(): void
    {
        $this->app->bind(CollectionDataLoader::class, function (Container $app) {
            return new CollectionDataLoader($app['files'], $app['consoleOutput'], $app[CollectionPathResolver::class], [
                $app[MarkdownCollectionItemHandler::class],
                $app[BladeCollectionItemHandler::class],
            ]);
        });

        $this->app->bind(DataLoader::class, function (Container $app) {
            return new DataLoader($app[CollectionDataLoader::class]);
        });

        $this->app->bind(CollectionRemoteItemLoader::class, function (Container $app) {
            return new CollectionRemoteItemLoader($app['config'], $app['files']);
        });
    }

    private function registerPaginator(): void
    {
        $this->app->bind(CollectionPaginator::class, function (Container $app) {
            return new CollectionPaginator($app['outputPathResolver']);
        });

        $this->app->bind(PaginatedPageHandler::class, function (Container $app) {
            return new PaginatedPageHandler($app[CollectionPaginator::class], $app[FrontMatterParser::class], $app[TemporaryFilesystem::class], $app[ViewRenderer::class]);
        });
    }

    private function registerSiteBuilder(): void
    {
        $this->app->bind(SiteBuilder::class, function (Container $app) {
            return new SiteBuilder($app['files'], $app->cachePath(), $app['outputPathResolver'], $app['consoleOutput'], [
                $app[CollectionItemHandler::class],
                new IgnoredHandler,
                $app[PaginatedPageHandler::class],
                $app[MarkdownHandler::class],
                $app[BladeHandler::class],
                $app[DefaultHandler::class],
            ]);
        });
    }
}
