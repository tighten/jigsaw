<?php

namespace TightenCo\Jigsaw\Providers;

use Mni\FrontYAML\Bridge\Symfony\SymfonyYAMLParser;
use Mni\FrontYAML\Markdown\MarkdownParser as FrontYAMLMarkdownParser;
use Mni\FrontYAML\Parser;
use Mni\FrontYAML\YAML\YAMLParser;
use TightenCo\Jigsaw\Container;
use TightenCo\Jigsaw\Parsers\CommonMarkParser;
use TightenCo\Jigsaw\Parsers\FrontMatterParser;
use TightenCo\Jigsaw\Parsers\JigsawMarkdownParser;
use TightenCo\Jigsaw\Parsers\MarkdownParser;
use TightenCo\Jigsaw\Parsers\MarkdownParserContract;
use TightenCo\Jigsaw\Support\ServiceProvider;

class MarkdownServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(YAMLParser::class, SymfonyYAMLParser::class);

        $this->app->bind(MarkdownParserContract::class, function (Container $app) {
            return $app['config']->get('commonmark') ? new CommonMarkParser : new JigsawMarkdownParser;
        });

        $this->app->singleton('markdownParser', fn (Container $app) => new MarkdownParser($app[MarkdownParserContract::class]));

        // Make the FrontYAML package use our own Markdown parser internally
        $this->app->bind(FrontYAMLMarkdownParser::class, fn (Container $app) => $app['markdownParser']);

        $this->app->bind(Parser::class, function (Container $app) {
            return new Parser($app[YAMLParser::class], $app[FrontYAMLMarkdownParser::class]);
        });

        $this->app->bind(FrontMatterParser::class, function (Container $app) {
            return new FrontMatterParser($app[Parser::class]);
        });
    }
}
