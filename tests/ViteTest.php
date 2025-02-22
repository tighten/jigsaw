<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Test;
use TightenCo\Jigsaw\Jigsaw;
use TightenCo\Jigsaw\Support\Vite;

class ViteTest extends TestCase
{
    #[Test]
    public function returns_dev_server_url_in_dev_mode()
    {
        $this->createSource(['source' => ['hot' => 'http://localhost:3000']]);
        app(Jigsaw::class)->setSourcePath("{$this->tmp}/source");

        $this->assertSame((new Vite)->devServer()->toHtml(), '<script type="module" src="http://localhost:3000/@vite/client"></script>');
    }

    #[Test]
    public function early_returns_dev_server_when_no_hot_file()
    {
        $this->assertEmpty((new Vite)->devServer());
    }

    #[Test]
    public function returns_build_asset_url()
    {
        $manifest = json_encode([
            'source/_assets/js/main.js' => [
                'file' => $url = 'assets/app.versioned.js',
            ],
            'source/_assets/css/main.css' => [
                'file' => 'assets/app.versioned.css',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->createSource(['source' => [
            'assets' => [
                'build' => [
                    'manifest.json' => $manifest,
                ],
            ],
        ]]);

        app(Jigsaw::class)->setSourcePath("{$this->tmp}/source");

        $this->assertEquals((new Vite)->url('source/_assets/js/main.js'), '/assets/build/' . $url);
    }

    #[Test]
    public function returns_dev_url()
    {
        $this->createSource(['source' => ['hot' => 'http://localhost:3000']]);
        app(Jigsaw::class)->setSourcePath("{$this->tmp}/source");

        $this->assertEquals((new Vite)->url($url = 'source/_assets/js/main.js'), 'http://localhost:3000/' . $url);
    }
}
