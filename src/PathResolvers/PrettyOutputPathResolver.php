<?php

namespace TightenCo\Jigsaw\PathResolvers;

class PrettyOutputPathResolver
{
    public function link($path, $name, $type, $page = 1)
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

    public function path($path, $name, $type, $page = 1)
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

    public function directory($path, $name, $type, $page = 1)
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
