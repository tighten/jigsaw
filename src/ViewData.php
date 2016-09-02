<?php namespace TightenCo\Jigsaw;

use Exception;
use TightenCo\Jigsaw\IterableObject;

class ViewData extends IterableObject
{
    private $data;
    private $globals = ['extends', 'section', 'link'];
    public $item;

    public static function withCollectionItem($data, $collectionName, $itemName)
    {
        $viewData = new static($data);
        $viewData->setCollectionItem($collectionName, $itemName);

        return $viewData;
    }

    public function __call($method, $args)
    {
        return $this->getHelper($method)->__invoke($this, ...$args);
    }

    private function getHelper($name)
    {
        $helper = $this->has('helpers') ? $this->helpers->{$name} : null;

        return $helper ?: function() use ($name) {
            throw new Exception("No helper function named '$name' in 'config.php'.");
        };
    }

    private function setCollectionItem($collection, $item)
    {
        if ($this->has($collection)) {
            $this->item = $this->get($collection)->get($item);
            $this->addSingularCollectionReference($collection);
            $this->setGloballyAvailableItemVariables();
        }
    }

    private function addSingularCollectionReference($collection)
    {
        if (str_singular($collection) != $collection) {
            $this->{str_singular($collection)} = $this->item;
        };
    }

    private function setGloballyAvailableItemVariables()
    {
        collect($this->globals)->each(function ($variable) {
            $this[$variable] = $this->item->{$variable};
        });
    }
}
