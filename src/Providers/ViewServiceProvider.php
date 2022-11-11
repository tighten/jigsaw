<?php

namespace TightenCo\Jigsaw\Providers;

use Illuminate\View\DynamicComponent;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\FileEngine;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use TightenCo\Jigsaw\Container;
use TightenCo\Jigsaw\File\BladeDirectivesFile;
use TightenCo\Jigsaw\File\TemporaryFilesystem;
use TightenCo\Jigsaw\Parsers\FrontMatterParser;
use TightenCo\Jigsaw\Support\ServiceProvider;
use TightenCo\Jigsaw\View\BladeCompiler;
use TightenCo\Jigsaw\View\BladeMarkdownEngine;
use TightenCo\Jigsaw\View\MarkdownEngine;
use TightenCo\Jigsaw\View\ViewRenderer;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerFactory();
        $this->registerViewFinder();
        $this->registerBladeCompiler();
        $this->registerEngineResolvers();

        (new BladeDirectivesFile($this->app->path('blade.php'), $this->app['blade.compiler']))->register();
        $this->app->bind(ViewRenderer::class, fn () => new ViewRenderer);
        $this->app->bind(TemporaryFilesystem::class, fn (Container $app) => new TemporaryFilesystem($app->cachePath()));

        // TODO
        // $this->registerExtensionEngines();
        // $this->registerConfiguredHintPaths();
    }

    private function registerFactory(): void
    {
        $this->app->singleton('view', function (Container $app) {
            $factory = new Factory($app['view.engine.resolver'], $app['view.finder'], $app['dispatcher']);

            $factory->setContainer($app);
            // TODO provide a magic `$app` variable to all views?
            // $factory->share('app', $app);

            return $factory;
        });
    }

    private function registerViewFinder(): void
    {
        $this->app->bind('view.finder', function (Container $app) {
            // TODO $app['config']['view.paths']
            return new FileViewFinder($app['files'], [$app->cachePath(), $app['buildPath']['views']]);
        });
    }

    private function registerBladeCompiler(): void
    {
        $this->app->singleton('blade.compiler', function (Container $app) {
            // TODO $app['config']['view.compiled']
            return tap(new BladeCompiler($app['files'], $app->cachePath()), function ($blade) {
                $blade->component('dynamic-component', DynamicComponent::class);
            });
        });

        // v1 binding is 'bladeCompiler'
        $this->app->alias('blade.compiler', 'bladeCompiler');
    }

    private function registerEngineResolvers(): void
    {
        $this->app->singleton('view.engine.resolver', function (Container $app) {
            $resolver = new EngineResolver;
            $compilerEngine = new CompilerEngine($app['blade.compiler'], $app['files']);

            // Same as Laravel
            $resolver->register('file', fn () => new FileEngine($app['files']));
            $resolver->register('php', fn () => new PhpEngine($app['files']));
            $resolver->register('blade', fn () => $compilerEngine);

            // Specific to Jigsaw
            // TODO $app['config']['view.paths']
            $resolver->register('markdown', fn () => new MarkdownEngine($app[FrontMatterParser::class], $app['files'], $app['buildPath']['views']));
            $resolver->register('blade-markdown', fn () => new BladeMarkdownEngine($compilerEngine, $this->app[FrontMatterParser::class]));

            return $resolver;
        });
    }

    private function registerExtensionEngines(): void
    {
        foreach (['md', 'markdown', 'mdown'] as $extension) {
            $this->app['view']->addExtension($extension, 'markdown');
            $this->app['view']->addExtension("blade.{$extension}", 'blade-markdown');
        }

        foreach (['js', 'json', 'xml', 'yaml', 'yml', 'rss', 'atom', 'txt', 'text', 'html'] as $extension) {
            $this->app['view']->addExtension($extension, 'php');
            $this->app['view']->addExtension("blade.{$extension}", 'blade');
        }
    }

    private function registerConfiguredHintPaths(): void
    {
        foreach ($this->app['config']->get('viewHintPaths', []) as $hint => $path) {
            $this->app['view']->addNamespace($hint, $path);
        }
    }
}
