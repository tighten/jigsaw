<?php namespace TightenCo\Jigsaw;

class BasicOutputPathResolver
{
    public function link($path, $name, $type, $page = 1)
    {
        if ($page > 1) {
            return $this->clean('/' . $path . '/' . $page . '/' . $name . '.' . $type);
        }
        return $this->clean('/' . $path . '/' . $name . '.' . $type);
    }

    public function path($path, $name, $type, $page = 1)
    {
        return $this->link($path, $name, $type, $page);
    }

    public function directory($path, $name, $type, $page = 1)
    {
        if ($page > 1) {
            return $this->clean($path . '/' . $page);
        }
        return $this->clean($path);
    }

    private function clean($path)
    {
        return str_replace('//', '/', $path);
    }
}
