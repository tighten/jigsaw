<?php

namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class BladeDirectivesTest extends TestCase
{
    #[Test]
    #[DataProvider('environmentDirectivesData')]
    public function render_environment_directives(string $environment, string $expected)
    {
        $this->app['env'] = $environment;

        $files = $this->setupSource([
            'page.blade.php' => implode("\n", [
                '<div>',
                '@production',
                'Confirmed in production',
                '@endproduction',
                "@env('local')",
                'Confirmed in development',
                '@endenv',
                '</div>',
            ]),
        ]);

        $this->buildSite($files);

        $this->assertOutputFile('build/page.html', $expected);
    }

    public static function environmentDirectivesData(): array
    {
        return [
            [
                'production',
                <<<'HTML'
                <div>
                Confirmed in production
                </div>
                HTML,
            ],
            [
                'local',
                <<<'HTML'
                <div>
                Confirmed in development
                </div>
                HTML,
            ],
            [
                'local',
                <<<'HTML'
                <div>
                Confirmed in development
                </div>
                HTML,
            ],
        ];
    }
}
