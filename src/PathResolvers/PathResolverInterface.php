<?php

namespace TightenCo\Jigsaw\PathResolvers;

interface PathResolverInterface
{
    public function link(string $path, string $name, string $type, int $page = 1);
    public function path(string $path, string $name, string $type, int $page = 1);
    public function directory(string $path, string $name, string $type, int $page = 1);
}
