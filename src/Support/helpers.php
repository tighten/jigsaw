<?php

declare(strict_types=1);

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Remove slashes (including backslashes on Windows),
 * spaces, and periods from the beginning and/or end of paths.
 */
function leftTrimPath(string $path): string
{
    return ltrim($path, ' .\\/');
}

function rightTrimPath(string $path): string
{
    return rtrim($path, ' .\\/');
}

function trimPath(string $path): string
{
    return rightTrimPath(leftTrimPath($path));
}

function resolvePath(string $path): string
{
    $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    $segments = [];

    collect(explode(DIRECTORY_SEPARATOR, $path))->filter()->each(function ($part) use (&$segments): void {
        if ($part == '..') {
            array_pop($segments);
        } elseif ($part != '.') {
            $segments[] = $part;
        }
    });

    return implode(DIRECTORY_SEPARATOR, $segments);
}

/**
 * Get the path to the public folder.
 */
function public_path(string $path = ''): string
{
    return 'source' . ($path ? '/' . $path : $path);
}

/**
 * Get the path to a versioned Elixir file.
 */
function elixir(string $file, string $buildDirectory = 'build'): string
{
    static $manifest;
    static $manifestPath;

    if (is_null($manifest) || $manifestPath !== $buildDirectory) {
        $manifest = json_decode(file_get_contents(public_path($buildDirectory . '/rev-manifest.json')), true);

        $manifestPath = $buildDirectory;
    }

    if (isset($manifest[$file])) {
        return '/' . trim($buildDirectory . '/' . $manifest[$file], '/');
    }

    throw new InvalidArgumentException("File {$file} not defined in asset manifest.");
}

/**
 * Get the path to a versioned Mix file.
 */
function mix(string $path, string $manifestDirectory = 'assets'): HtmlString
{
    static $manifests = [];

    if (! Str::startsWith($path, '/')) {
        $path = "/{$path}";
    }

    if ($manifestDirectory && ! Str::startsWith($manifestDirectory, '/')) {
        $manifestDirectory = "/{$manifestDirectory}";
    }

    if (file_exists(public_path($manifestDirectory . '/hot'))) {
        return new HtmlString("//localhost:8080{$path}");
    }

    $manifestPath = public_path($manifestDirectory . '/mix-manifest.json');

    if (! isset($manifests[$manifestPath])) {
        if (! file_exists($manifestPath)) {
            throw new Exception('The Mix manifest does not exist.');
        }

        $manifests[$manifestPath] = json_decode(file_get_contents($manifestPath), true);
    }

    $manifest = $manifests[$manifestPath];

    if (! isset($manifest[$path])) {
        throw new InvalidArgumentException("Unable to locate Mix file: {$path}.");
    }

    return new HtmlString($manifestDirectory . $manifest[$path]);
}

if (! function_exists('dd')) {
    function dd(...$args): void
    {
        foreach ($args as $x) {
            (new VarDumper())->dump($x);
        }

        die(1);
    }
}
