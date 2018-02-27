<?php namespace TightenCo\Jigsaw\Collection;

use TightenCo\Jigsaw\PageVariable;

class CollectionItem extends PageVariable
{
    private $collection;

    public static function build($collection, $data)
    {
        $item = new static($data);
        $item->collection = $collection;

        return $item;
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
        return $this->_content;
    }

    public function __toString()
    {
        return (String) $this->getContent();
    }

    protected function missingHelperError($functionName)
    {
        return 'No function named "' . $functionName. '" for the collection "' . $this->_meta->collectionName . '" was found in the file "config.php".';
    }
}
