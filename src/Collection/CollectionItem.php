<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Collection;

use Illuminate\Support\Collection;
use TightenCo\Jigsaw\PageVariable;

class CollectionItem extends PageVariable
{
    /** @var Collection */
    private $collection;

    /** @var callable|string */
    private $_content;

    public static function build(Collection $collection, $data): CollectionItem
    {
        $item = new static($data);
        $item->collection = $collection;

        return $item;
    }

    public function getNext(): ?CollectionItem
    {
        return $this->_meta->nextItem ? $this->collection->get($this->_meta->nextItem) : null;
    }

    public function getPrevious(): ?CollectionItem
    {
        return $this->_meta->previousItem ? $this->collection->get($this->_meta->previousItem) : null;
    }

    public function getFirst(): CollectionItem
    {
        return $this->collection->first();
    }

    public function getLast(): CollectionItem
    {
        return $this->collection->last();
    }

    /**
     * @param string|callable $content
     */
    public function setContent($content): void
    {
        $this->_content = $content;
    }

    public function getContent(): string
    {
        return is_callable($this->_content) ?
            call_user_func($this->_content) :
            $this->_content;
    }

    public function __toString(): string
    {
        return (string) $this->getContent();
    }

    protected function missingHelperError(string $functionName): string
    {
        return 'No function named "' . $functionName . '" for the collection "' . $this->_meta->collectionName . '" was found in the file "config.php".';
    }
}
