<?php namespace TightenCo\Jigsaw\PathResolvers;

class PrettyOutputPathResolver
{
    public function link($path, $name, $type, $page = 1)
    {
        if ($type === 'html' && $name === 'index') {
            if ($page > 1) {
                return '/' . ltrim($this->trimPath($path) . '/', '/') . $page . '/';
            }
            return  ltrim('/' . $this->trimPath($path) . '/', '/') . '/';
        }

        if ($type === 'html' && $name !== 'index') {
            if ($page > 1) {
                return '/' . ltrim($this->trimPath($path) . '/', '/') . $name . '/' . $page . '/';
            }
            return '/' . ltrim($this->trimPath($path) . '/', '/') . $name . '/';
        }

        return sprintf('%s%s%s.%s', '/', ltrim($this->trimPath($path) . '/', '/'), $name, $type);
    }

    public function path($path, $name, $type, $page = 1)
    {
        if ($type === 'html' && $name === 'index' && $page > 1) {
            return ltrim($this->trimPath($path) . '/' . $page . '/' . 'index.html', '/');
        }

        if ($type === 'html' && $name !== 'index') {
            if ($page > 1) {
                return  $this->trimPath($path) . '/' . $name . '/' . $page . '/' . 'index.html';
            }
            return $this->trimPath($path) . '/' . $name . '/' . 'index.html';
        }

        if (empty($type)) {
            return sprintf('%s%s%s', $this->trimPath($path), '/', $name);
        }

        return sprintf('%s%s%s.%s', $this->trimPath($path), '/', $name, $type);
    }

    public function directory($path, $name, $type, $page = 1)
    {
        if ($type === 'html' && $name === 'index' && $page > 1) {
            return ltrim($this->trimPath($path) . '/' . $page, '/');
        }

        if ($type === 'html' && $name !== 'index') {
            if ($page > 1) {
                return  $this->trimPath($path) . '/' . $name . '/' . $page;
            }
            return $this->trimPath($path) . '/' . $name;
        }

        return $this->trimPath($path);
    }

    private function trimPath($path)
    {
        return rtrim(ltrim($path, '/'), '/');
    }
}
