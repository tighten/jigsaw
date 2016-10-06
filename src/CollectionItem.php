<?php namespace TightenCo\Jigsaw;

use TightenCo\Jigsaw\IterableObject;
use TightenCo\Jigsaw\HelperFunctionTrait;

class CollectionItem extends IterableObject
{
    use HelperFunctionTrait;

    private $collection;

    public static function build($collection, $data)
    {
        $item = new static($data);
        $item->collection = $collection;

        return $item;
    }

    public function getHelper($name)
    {
        return $this->collection->getHelper($name);
    }

    public function getNext()
    {
        return $this->_nextItem ? $this->collection->get($this->_nextItem) : null;
    }

    public function getPrevious()
    {
        return $this->_previousItem ? $this->collection->get($this->_previousItem) : null;
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

    private function missingHelperError($function_name)
    {
        return 'No helper function named "' . $function_name. '" for the collection "' . $this->get('collection') . '" was found in the file "collections.php".';
    }
}
