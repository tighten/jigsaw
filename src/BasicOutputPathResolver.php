<?php namespace TightenCo\Jigsaw;

class BasicOutputPathResolver
{
    public function link($path, $name, $type)
    {
        return sprintf('%s%s%s.%s', $this->trimPath($path), DIRECTORY_SEPARATOR, $name, $type);
    }

    public function path($path, $name, $type)
    {
        return $this->link($path, $name, $type);
    }

    public function directory($path, $name, $type)
    {
        return $this->trimPath($path);
    }

    private function trimPath($path)
    {
        return rtrim(ltrim($path, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
    }
}
