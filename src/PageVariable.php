<?php

namespace TightenCo\Jigsaw;

use Illuminate\Support\Str;

class PageVariable extends IterableObject
{
    public function addVariables(array $variables)
    {
        $this->items = collect($this->items)->merge($this->makeIterable($variables))->all();
    }

    public function __call($method, $args)
    {
        $helper = $this->get($method);

        if (! $helper && Str::startsWith($method, 'get')) {
            return $this->_meta->get(Str::camel(substr($method, 3)), function () use ($method) {
                throw new \Exception($this->missingHelperError($method));
            });
        }

        if (is_callable($helper)) {
            return $helper->__invoke($this, ...$args);
        } else {
            throw new \Exception($this->missingHelperError($method));
        }
    }

    public function getPath($key = null)
    {
        if (($key || $this->_meta->extending) && $this->_meta->path instanceof IterableObject) {
            return $this->_meta->path->get($key ?: $this->getExtending());
        }

        return (string) $this->_meta->path;
    }

    public function getPaths()
    {
        return $this->_meta->path;
    }

    public function getUrl($key = null)
    {
        if (($key || $this->_meta->extending) && $this->_meta->path instanceof IterableObject) {
            return $this->_meta->url->get($key ?: $this->getExtending());
        }

        return (string) $this->_meta->url;
    }

    public function getUrls()
    {
        return $this->_meta->url;
    }

    protected function missingHelperError($functionName)
    {
        return 'No function named "' . $functionName . '" was found in the file "config.php".';
    }
}
