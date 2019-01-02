<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\PathResolvers;

use TightenCo\Jigsaw\Contracts\PathResolver;

class BasicOutputPathResolver implements PathResolver
{
    public function link(string $path, string $name, string $type, int $page = 1): string
    {
        $extension = $type ? '.' . $type : '';
        $name = basename($name, $extension);

        return $page > 1 ?
            $this->clean('/' . $path . '/' . $page . '/' . $name . $extension) :
            $this->clean('/' . $path . '/' . $name . $extension);
    }

    public function path(string $path, string $name, string $type, int $page = 1): string
    {
        return $this->link($path, $name, $type, $page);
    }

    public function directory(string $path, string $name, string $type, int $page = 1): string
    {
        return $page > 1 ?
            $this->clean($path . '/' . $page) :
            $this->clean($path);
    }

    private function clean(string $path): string
    {
        return str_replace('//', '/', $path);
    }
}
