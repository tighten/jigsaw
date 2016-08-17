<?php

if (! function_exists('slugify')) {
    /**
     * Convert a filename into a URL slug.
     *
     * @param  string  $filename
     * @param  string  $delimiter
     * @return string
     */
    function slugify($filename, $delimiter = '-')
    {
        setlocale(LC_ALL, 'en_US.UTF8');
        $convertSpecialCharacters = iconv('UTF-8', 'ASCII//TRANSLIT', trim($filename));
        $removePunctuation = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $convertSpecialCharacters);
        $lowerCase = strtolower($removePunctuation);
        $delimitedSlug = preg_replace("/[_|+ -]+/", $delimiter, $lowerCase);

        return $delimitedSlug;
    }
}

if (! function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string  $path
     * @return string
     */
    function public_path($path = '')
    {
        return 'source'.($path ? '/'.$path : $path);
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

        throw new InvalidArgumentException("File {$file} not defined in asset manifest.");
    }
}
