<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\PathResolvers;

use TightenCo\Jigsaw\Contracts\PathResolver;

class PrettyOutputPathResolver implements PathResolver
{
    public function link(string $path, string $name, string $type, int $page = 1): string
    {
        if ($type === 'html' && $name === 'index') {
            if ($page > 1) {
                return '/' . leftTrimPath(trimPath($path) . '/') . $page . '/';
            }

            return  leftTrimPath('/' . trimPath($path) . '/') . '/';
        }

        if ($type === 'html' && $name !== 'index') {
            if ($page > 1) {
                return '/' . leftTrimPath(trimPath($path) . '/') . $name . '/' . $page . '/';
            }

            return '/' . leftTrimPath(trimPath($path) . '/') . $name . '/';
        }

        return sprintf('%s%s%s.%s', '/', leftTrimPath(trimPath($path) . '/'), $name, $type);
    }

    public function path(string $path, string $name, string $type, int $page = 1): string
    {
        if ($type === 'html' && $name === 'index' && $page > 1) {
            return leftTrimPath(trimPath($path) . '/' . $page . '/' . 'index.html');
        }

        if ($type === 'html' && $name !== 'index') {
            if ($page > 1) {
                return  trimPath($path) . '/' . $name . '/' . $page . '/' . 'index.html';
            }

            return trimPath($path) . '/' . $name . '/' . 'index.html';
        }

        if (empty($type)) {
            return sprintf('%s%s%s', trimPath($path), '/', $name);
        }

        return sprintf('%s%s%s.%s', trimPath($path), '/', $name, $type);
    }

    public function directory(string $path, string $name, string $type, int $page = 1): string
    {
        if ($type === 'html' && $name === 'index' && $page > 1) {
            return leftTrimPath(trimPath($path) . '/' . $page);
        }

        if ($type === 'html' && $name !== 'index') {
            if ($page > 1) {
                return  trimPath($path) . '/' . $name . '/' . $page;
            }

            return trimPath($path) . '/' . $name;
        }

        return trimPath($path);
    }
}
