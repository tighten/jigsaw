<?php

/**
 * Remove slashes (including backslashes on Windows),
 * spaces, and periods from the beginning and/or end of paths.
 */
function leftTrimPath($path)
{
    return ltrim($path, " .\\/");
}

function rightTrimPath($path)
{
    return rtrim($path, " .\\/");
}

function trimPath($path)
{
    return rightTrimPath(leftTrimPath($path));
}

/**
 * Get the path to the public folder.
 */
function public_path($path = '')
{
    return 'source' . ($path ? '/' . $path : $path);
}

/**
 * Get the path to a versioned Elixir file.
 */
function elixir($file, $buildDirectory = 'build')
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
