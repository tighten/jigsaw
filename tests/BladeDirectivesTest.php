<?php

namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class BladeDirectivesTest extends TestCase
{
    #[Test]
    #[DataProvider('environmentDirectivesData')]
    public function render_environment_directives($config, $expected)
    {
        $files = $this->setupSource([
            'page.blade.php' => implode("\n", [
                '<div>',
                '@production',
                'Confirmed in production',
                '@endproduction',
                "@env('development')",
                'Confirmed in development',
                '@endenv',
                '</div>',
            ]),
        ]);

        $this->buildSite($files, $config);

        $this->assertOutputFile('build/page.html', $expected);
    }

    public static function environmentDirectivesData(): array
    {
        return [
            [
                ['production' => true], <<<'HTML'
                <div>
                Confirmed in production
                </div>
                HTML,
            ],
            [
                ['production' => false], <<<'HTML'
                <div>
                Confirmed in development
                </div>
                HTML,
            ],
            [
                [], <<<'HTML'
                <div>
                Confirmed in development
                </div>
                HTML,
            ],
        ];
    }
}
