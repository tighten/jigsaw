<?php

namespace TightenCo\Jigsaw\Support;

use Exception;
use Illuminate\Support\HtmlString;

class Vite
{
    private function hotFilePath()
    {
        return source_path('hot');
    }

    public function url(string $asset, $assetPath = '/assets/build')
    {

        $hotFilePath = $this->hotFilePath();

        $dev = false;
        try {
            $dev = file_get_contents($hotFilePath);
        } catch (Exception $e) {
        }

        if ($dev) {
            $devServerUrl = $dev;

            return $devServerUrl . '/' . $asset;
        }

        $manifestPath = source_path($assetPath . '/manifest.json');
        $manifest = $this->loadManifest($manifestPath);

        if (! isset($manifest[$asset])) {
            throw new Exception('Main entry point not found in Vite manifest.');
        }

        $manifestEntry = $manifest[$asset];

        return $assetPath . "/{$manifestEntry['file']}";
    }

    public function devServer()
    {
        $hotFilePath = $this->hotFilePath();

        $devServerUrl = false;
        try {
            $devServerUrl = file_get_contents($hotFilePath);
        } catch (Exception $e) {
        }

        if (! $devServerUrl) {
            return;
        }

        return new HtmlString(sprintf('<script type="module" src="%s"></script>', "{$devServerUrl}/@vite/client"));
    }

    private function loadManifest($manifestPath)
    {
        static $manifests = [];

        return $manifests[$manifestPath] ??= $this->uncachedManifest($manifestPath);
    }

    private function uncachedManifest($manifestPath)
    {
        if (! file_exists($manifestPath)) {
            throw new Exception('The Vite manifest does not exist. Please run `npm run build` first or start the dev server.');
        }

        return json_decode(file_get_contents($manifestPath), true);
    }
}
