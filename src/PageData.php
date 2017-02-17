<?php namespace TightenCo\Jigsaw;

use TightenCo\Jigsaw\IterableObject;
use TightenCo\Jigsaw\PageVariable;

class PageData extends IterableObject
{
    public static function withPageMetaData($siteData, $meta)
    {
        $page_data = new static($siteData->except('page'));
        $page_data->put('page', (new PageVariable($siteData->page))->put('_meta', new IterableObject($meta)));

        return $page_data;
    }

    public function setPageVariableToCollectionItem($collectionName, $itemName)
    {
        $this->put('page', $this->get($collectionName)->get($itemName));
        $this->addSingularCollectionReference($collectionName);
    }

    private function addSingularCollectionReference($collectionName)
    {
        $singular_collectionName = str_singular($collectionName);

        if ($singular_collectionName != $collectionName) {
            $this->put($singular_collectionName, $this->get('page'));
        };
    }
}
