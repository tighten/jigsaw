<?php namespace TightenCo\Jigsaw;

class BasicOutputPathResolver
{
    public function link($path, $name, $type, $page = 1)
    {
        if ($page > 1) {
            return sprintf('%s%s%s%s%s.%s', $this->trimPath($path), DIRECTORY_SEPARATOR, $name, DIRECTORY_SEPARATOR, $page, $type);
        }
        return sprintf('%s%s%s.%s', $this->trimPath($path), DIRECTORY_SEPARATOR, $name, $type);
    }

    public function path($path, $name, $type, $page = 1)
    {
        return $this->link($path, $name, $type, $page);
    }

    public function directory($path, $name, $type, $page = 1)
    {
        if ($page > 1) {
            return $this->trimPath($path) . DIRECTORY_SEPARATOR . $name;
        }
        return $this->trimPath($path);
    }

    private function trimPath($path)
    {
        return rtrim(ltrim($path, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
    }
}
