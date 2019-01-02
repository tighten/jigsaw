<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Contracts;

interface PathResolver
{
    public function link(string $path, string $name, string $type, int $page = 1): string;

    public function path(string $path, string $name, string $type, int $page = 1): string;

    public function directory(string $path, string $name, string $type, int $page = 1): string;
}
