<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Collection;

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

    public function getContent(): string
    {
        return is_array($this->item) ?
            $this->getHeader() . array_get($this->item, 'content') :
            $this->item;
    }

    public function getFilename(): string
    {
        return array_get($this->item, 'filename', $this->prefix . ($this->index + 1)) . '.md';
    }

    protected function getHeader(): ?string
    {
        $variables = collect($this->item)->except('content')->toArray();

        return count($variables) ? "---\n" . Yaml::dump($variables) . "---\n" : null;
    }
}
