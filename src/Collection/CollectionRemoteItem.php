<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Collection;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use JsonSerializable;
use Symfony\Component\Yaml\Yaml;
use Traversable;

class CollectionRemoteItem
{
    /** @var array|string */
    private $item;

    /** @var int */
    private $index;

    /** @var string */
    private $prefix;

    /**
     * @param array|Collection|Arrayable|Jsonable|JsonSerializable|Traversable|string $item
     */
    public function __construct($item, int $index = 0, ?string $collectionName = null)
    {
        $this->item = is_string($item) ? $item : collect($item)->toArray();
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
