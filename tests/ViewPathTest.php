<?php

namespace Tests;

use org\bovigo\vfs\vfsStream;

class ViewPathTest extends TestCase
{
    /** @test */
    public function can_load_views_from_custom_path()
    {
        $files = vfsStream::setup('virtual', null, [
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

        $this->buildSite($files, [], false, '/views');

        $this->assertSame(<<<HTML
            <body>
                <h1>Hello world!</h1>
            </body>
            HTML,
            $files->getChild('build/page.html')->getContent()
        );
    }
}
