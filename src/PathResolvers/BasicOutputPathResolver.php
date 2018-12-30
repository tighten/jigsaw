<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\PathResolvers;

class BasicOutputPathResolver
{
    public function link($path, $name, $type, $page = 1): string
    {
        $extension = $type ? '.' . $type : '';
        $name = basename($name, $extension);

        return $page > 1 ?
            $this->clean('/' . $path . '/' . $page . '/' . $name . $extension) :
            $this->clean('/' . $path . '/' . $name . $extension);
    }

    public function path($path, $name, $type, $page = 1): string
    {
        return $this->link($path, $name, $type, $page);
    }

    public function directory($path, $name, $type, $page = 1): string
    {
        return $page > 1 ?
            $this->clean($path . '/' . $page) :
            $this->clean($path);
    }

    private function clean($path): string
    {
        return str_replace('//', '/', $path);
    }
}
