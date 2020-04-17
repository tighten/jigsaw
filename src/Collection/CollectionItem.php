<?php

namespace TightenCo\Jigsaw\Collection;

use TightenCo\Jigsaw\PageVariable;

class CollectionItem extends PageVariable
{
    public $collection;

    public static function build(Collection $collection, $data)
    {
        $item = new static($data);
        $item->collection = $collection;

        return $item;
    }

    public static function fromItem(CollectionItem $item)
    {
        $newItem = new static($item);
        $newItem->collection = $item->collection;

        return $newItem;
    }

    public function getNext()
    {
        return $this->_meta->nextItem ? $this->collection->get($this->_meta->nextItem) : null;
    }

    public function getPrevious()
    {
        return $this->_meta->previousItem ? $this->collection->get($this->_meta->previousItem) : null;
    }

    public function getFirst()
    {
        return $this->collection->first();
    }

    public function getLast()
    {
        return $this->collection->last();
    }

    public function setContent($content)
    {
        $this->_content = $content;
    }

    public function getContent()
    {
        return is_callable($this->_content) ?
            call_user_func($this->_content) :
            $this->_content;
    }

    public function __toString()
    {
        return (string) $this->getContent();
    }

    protected function missingHelperError($functionName)
    {
        return 'No function named "' . $functionName . '" for the collection "' . $this->_meta->collectionName . '" was found in the file "config.php".';
    }
}
