<?php

namespace TightenCo\Jigsaw\Collection;

use Illuminate\Support\Arr;
use Symfony\Component\Yaml\Yaml;

class CollectionRemoteItem
{
    private $item;
    private $index;
    private $prefix;

    public function __construct($item, $index = 0, $collectionName = null)
    {
        $this->item = $item;
        $this->index = $index;
        $this->prefix = $collectionName . '_';
    }

    public function getContent()
    {
        return is_array($this->item) ?
            $this->getHeader() . Arr::get($this->item, 'content') :
            $this->item;
    }

    public function getFilename()
    {
        return Arr::get($this->item, 'filename', $this->prefix . ($this->index + 1)) . '.blade.md';
    }

    protected function getHeader()
    {
        $variables = collect($this->item)->except('content')->toArray();

        return count($variables) ? "---\n" . Yaml::dump($variables) . "---\n" : null;
    }
}
