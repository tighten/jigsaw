<?php

/**
 * To rebuild the `snapshots` directory after changing
 * files in `source`, run `php tests/rebuild.php`.
 */
exec('jigsaw build testing');
removeDirectory('tests/snapshots');
rename('tests/build-testing', 'tests/snapshots');

function removeDirectory($path)
{
    $files = glob($path . '/*');

    foreach ($files as $file) {
        is_dir($file) ? removeDirectory($file) : unlink($file);
    }

    rmdir($path);

    return;
}

