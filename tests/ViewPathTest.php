<?php

namespace Tests;

class ViewPathTest extends TestCase
{
    /** @test */
    public function can_load_views_from_custom_path()
    {
        $this->createSource([
            'source' => [
                'page.md' => <<<MD
                ---
                extends: main
                ---
                # Hello world!
                MD,
            ],
            'views' => [
                'main.blade.php' => <<<BLADE
                <body>
                    @yield('content')
                </body>
                BLADE,
            ],
        ]);

        $this->buildSite(null, [], false, '/views');

        $this->assertOutputFile('build/page.html', <<<HTML
            <body>
                <h1>Hello world!</h1>
            </body>
            HTML,
        );
    }
}
