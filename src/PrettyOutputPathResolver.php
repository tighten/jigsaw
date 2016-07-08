<?php namespace TightenCo\Jigsaw;

class PrettyOutputPathResolver
{
    public function link($path, $name, $type, $page = 1)
    {
        if ($type === 'html' && $name === 'index') {
            if ($page > 1) {
                return  DIRECTORY_SEPARATOR . ltrim($this->trimPath($path) . DIRECTORY_SEPARATOR, '/') . $page . DIRECTORY_SEPARATOR;
            }
            return  ltrim(DIRECTORY_SEPARATOR . $this->trimPath($path) . DIRECTORY_SEPARATOR, '/') . DIRECTORY_SEPARATOR;
        }

        if ($type === 'html' && $name !== 'index') {
            if ($page > 1) {
                return  DIRECTORY_SEPARATOR . ltrim($this->trimPath($path) . DIRECTORY_SEPARATOR, '/') . $name . DIRECTORY_SEPARATOR . $page . DIRECTORY_SEPARATOR;
            }
            return DIRECTORY_SEPARATOR . ltrim($this->trimPath($path) . DIRECTORY_SEPARATOR, '/') . $name . DIRECTORY_SEPARATOR;
        }

        return sprintf('%s%s%s.%s', DIRECTORY_SEPARATOR, ltrim($this->trimPath($path) . DIRECTORY_SEPARATOR, '/'), $name, $type);
    }

    public function path($path, $name, $type, $page = 1)
    {
        if ($type === 'html' && $name === 'index' && $page > 1) {
            return ltrim($this->trimPath($path) . DIRECTORY_SEPARATOR . $page . DIRECTORY_SEPARATOR . 'index.html', DIRECTORY_SEPARATOR);
        }

        if ($type === 'html' && $name !== 'index') {
            if ($page > 1) {
                return  $this->trimPath($path) . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . $page . DIRECTORY_SEPARATOR . 'index.html';
            }
            return $this->trimPath($path) . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'index.html';
        }

        return sprintf('%s%s%s.%s', $this->trimPath($path), DIRECTORY_SEPARATOR, $name, $type);
    }

    public function directory($path, $name, $type, $page = 1)
    {
        if ($type === 'html' && $name === 'index' && $page > 1) {
            return ltrim($this->trimPath($path) . DIRECTORY_SEPARATOR . $page, DIRECTORY_SEPARATOR);
        }

        if ($type === 'html' && $name !== 'index') {
            if ($page > 1) {
                return  $this->trimPath($path) . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . $page;
            }
            return $this->trimPath($path) . DIRECTORY_SEPARATOR . $name;
        }

        return $this->trimPath($path);
    }

    private function trimPath($path)
    {
        return rtrim(ltrim($path, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
    }
}
