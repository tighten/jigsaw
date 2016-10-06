<?php namespace TightenCo\Jigsaw;

use Exception;
use TightenCo\Jigsaw\HelperFunctionTrait;
use TightenCo\Jigsaw\IterableObject;

class ViewData extends IterableObject
{
    private $data;
    private $globals = ['extends', 'section', 'path'];
    public $item;
    use HelperFunctionTrait;

    public static function withCollectionItem($data, $collectionName, $itemName)
    {
        $viewData = new static($data);
        $viewData->setCollectionItem($collectionName, $itemName);

        return $viewData;
    }

    public function getHelper($name)
    {
        return $this->config->getHelper($name);
    }

    private function missingHelperError($function_name)
    {
        return 'No helper function named "' . $function_name. '" was found in the file "config.php".';
    }

    private function setCollectionItem($collection, $item)
    {
        if ($this->has($collection)) {
            $this->put('item', $this->get($collection)->get($item));
            $this->addSingularCollectionReference($collection);
            $this->useItemSettings();
        }
    }

    private function addSingularCollectionReference($collectionName)
    {
        $singularCollectionName = str_singular($collectionName);

        if ($singularCollectionName != $collectionName) {
            $this->put($singularCollectionName, $this->get('item'));
        };
    }

    private function useItemSettings()
    {
        collect($this->item_settings)->each(function ($variable) {
            $this[$variable] = $this->get('item')->{$variable};
        });
    }
}
