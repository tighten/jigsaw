<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use JsonSerializable;
use Traversable;

/**
 * @property IterableObject $_meta
 * @property string $extends
 * @property string $section
 */
class PageVariable extends IterableObject
{
    /**
     * @param array|Collection|Arrayable|Jsonable|JsonSerializable|Traversable $variables
     */
    public function addVariables($variables): void
    {
        $this->items = collect($this->items)->merge($this->makeIterable($variables))->all();
    }

    public function __call(string $method, array $args)
    {
        $helper = $this->get($method);

        if (! $helper && starts_with($method, 'get')) {
            return $this->_meta->get(camel_case(substr($method, 3)), function () use ($method) {
                throw new \Exception($this->missingHelperError($method));
            });
        }

        if (is_callable($helper)) {
            return $helper->__invoke($this, ...$args);
        } else {
            throw new \Exception($this->missingHelperError($method));
        }
    }

    public function getPath(?string $key = null): string
    {
        if (($key || $this->_meta->extending) && $this->_meta->path instanceof IterableObject) {
            return $this->_meta->path->get($key ?: $this->getExtending());
        }

        return (string) $this->_meta->path;
    }

    public function getPaths(): Collection
    {
        return $this->_meta->path;
    }

    public function getUrl(?string $key = null): string
    {
        if (($key || $this->_meta->extending) && $this->_meta->path instanceof IterableObject) {
            return $this->_meta->url->get($key ?: $this->getExtending());
        }

        return (string) $this->_meta->url;
    }

    public function getUrls(): Collection
    {
        return $this->_meta->url;
    }

    protected function missingHelperError(string $functionName): string
    {
        return 'No function named "' . $functionName . '" was found in the file "config.php".';
    }
}
