<?php

namespace Tests;

use Exception;
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

    #[Test]
    public function throws_when_manifest_does_not_exist()
    {
        $this->createSource(['source' => []]);
        app(Jigsaw::class)->setSourcePath("{$this->tmp}/source");

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The Vite manifest does not exist.');

        (new Vite)->url('source/_assets/js/main.js');
    }

    #[Test]
    public function throws_when_asset_is_not_in_manifest()
    {
        $manifest = json_encode([
            'source/_assets/js/main.js' => ['file' => 'assets/app.versioned.js'],
        ], JSON_UNESCAPED_SLASHES);

        $this->createSource(['source' => [
            'assets' => ['build' => ['manifest.json' => $manifest]],
        ]]);

        app(Jigsaw::class)->setSourcePath("{$this->tmp}/source");

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Main entry point not found in Vite manifest.');

        (new Vite)->url('source/_assets/js/missing.js');
    }

    #[Test]
    public function returns_build_asset_url_with_custom_asset_path()
    {
        $manifest = json_encode([
            'source/_assets/js/main.js' => ['file' => 'assets/app.versioned.js'],
        ], JSON_UNESCAPED_SLASHES);

        $this->createSource(['source' => [
            'custom' => ['dist' => ['manifest.json' => $manifest]],
        ]]);

        app(Jigsaw::class)->setSourcePath("{$this->tmp}/source");

        $this->assertEquals(
            '/custom/dist/assets/app.versioned.js',
            (new Vite)->url('source/_assets/js/main.js', '/custom/dist'),
        );
    }

    #[Test]
    public function returns_consistent_results_across_multiple_calls_on_same_instance()
    {
        $manifest = json_encode([
            'source/_assets/js/main.js' => ['file' => 'assets/app.versioned.js'],
            'source/_assets/css/main.css' => ['file' => 'assets/app.versioned.css'],
        ], JSON_UNESCAPED_SLASHES);

        $this->createSource(['source' => [
            'assets' => ['build' => ['manifest.json' => $manifest]],
        ]]);

        app(Jigsaw::class)->setSourcePath("{$this->tmp}/source");

        $vite = new Vite;

        $this->assertSame($vite->url('source/_assets/js/main.js'), $vite->url('source/_assets/js/main.js'));
        $this->assertSame($vite->url('source/_assets/css/main.css'), $vite->url('source/_assets/css/main.css'));
    }

    #[Test]
    public function new_instance_reads_fresh_manifest_after_update()
    {
        $manifest = json_encode([
            'source/_assets/js/main.js' => ['file' => 'assets/app-v1.js'],
        ], JSON_UNESCAPED_SLASHES);

        $this->createSource(['source' => [
            'assets' => ['build' => ['manifest.json' => $manifest]],
        ]]);

        app(Jigsaw::class)->setSourcePath("{$this->tmp}/source");

        $firstUrl = (new Vite)->url('source/_assets/js/main.js');
        $this->assertStringContainsString('app-v1.js', $firstUrl);

        // Simulate a rebuild updating the manifest on disk
        $updatedManifest = json_encode([
            'source/_assets/js/main.js' => ['file' => 'assets/app-v2.js'],
        ], JSON_UNESCAPED_SLASHES);
        file_put_contents("{$this->tmp}/source/assets/build/manifest.json", $updatedManifest);

        // A new instance must read the updated file, not return stale cached data
        $secondUrl = (new Vite)->url('source/_assets/js/main.js');
        $this->assertStringContainsString('app-v2.js', $secondUrl);
    }
}
