<?php namespace TightenCo\Jigsaw;

class BasicOutputPathResolver
{
    public function link($path, $name, $type, $page = 1)
    {
        if ($page > 1) {
            return sprintf('%s%s%s%s%s.%s', $this->trimPath($path), '/', $page, '/', $name, $type);
        }
        return sprintf('%s%s%s.%s', $this->trimPath($path), '/', $name, $type);
    }

    public function path($path, $name, $type, $page = 1)
    {
        return $this->link($path, $name, $type, $page);
    }

    public function directory($path, $name, $type, $page = 1)
    {
        if ($page > 1) {
            return $this->trimPath($path) . '/' . $page;
        }
        return $this->trimPath($path);
    }

    private function trimPath($path)
    {
        return rtrim(ltrim($path, '/'), '/');
    }
}
