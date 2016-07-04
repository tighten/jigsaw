<?php namespace TightenCo\Jigsaw;

class PrettyOutputPathResolver
{
    public function link($path, $name, $type)
    {
        if ($type === 'html' && $name === 'index') {
            return  DIRECTORY_SEPARATOR . $this->trimPath($path) . DIRECTORY_SEPARATOR;
        }

        if ($type === 'html' && $name !== 'index') {
            return DIRECTORY_SEPARATOR . $this->trimPath($path) . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
        }

        return sprintf('%s%s%s%s.%s', DIRECTORY_SEPARATOR, $this->trimPath($path), DIRECTORY_SEPARATOR, $name, $type);
    }

    public function path($path, $name, $type)
    {
        if ($type === 'html' && $name !== 'index') {
            return $this->trimPath($path) . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'index.html';
        }

        return sprintf('%s%s%s.%s', $this->trimPath($path), DIRECTORY_SEPARATOR, $name, $type);
    }

    public function directory($path, $name, $type)
    {
        if ($type === 'html' && $name !== 'index') {
            return $this->trimPath($path) . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
        }

        return $this->trimPath($path);
    }

    private function trimPath($path)
    {
        return rtrim(ltrim($path, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
    }
}
