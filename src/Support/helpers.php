<?php

if (! function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string  $path
     * @return string
     */
    function public_path($path = '')
    {
        return 'source'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (! function_exists('elixir')) {
    /**
     * Get the path to a versioned Elixir file.
     *
     * @param  string  $file
     * @param  string  $buildDirectory
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    function elixir($file, $buildDirectory = 'build')
    {
        static $manifest;
        static $manifestPath;

        if (is_null($manifest) || $manifestPath !== $buildDirectory) {
            $manifest = json_decode(file_get_contents(public_path($buildDirectory.'/rev-manifest.json')), true);

            $manifestPath = $buildDirectory;
        }

        if (isset($manifest[$file])) {
            return '/'.trim($buildDirectory.'/'.$manifest[$file], '/');
        }

        return $file;

        throw new InvalidArgumentException("File {$file} not defined in asset manifest.");
    }
}
