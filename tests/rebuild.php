<?php

/**
 * To rebuild the `snapshots` directory after changing
 * files in `source`, run `php tests/rebuild.php`.
 */
shell_exec('./jigsaw build testing');
removeDirectory('tests/snapshots');
rename('tests/build-testing', 'tests/snapshots');

function removeDirectory($path)
{
    if (! $path) {
        exit("Path to the 'tests/snapshots' directory is missing");
    }

    $files = glob($path . '/{,.}[!.,!..]*', GLOB_MARK|GLOB_BRACE);

    foreach ($files as $file) {
        is_dir($file) ? removeDirectory($file) : unlink($file);
    }

    rmdir($path);

    return;
}
