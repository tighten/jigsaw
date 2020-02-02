<?php

namespace TightenCo\Jigsaw;

class PageData extends IterableObject
{
    public static function withPageMetaData(IterableObject $siteData, array $meta)
    {
        $page_data = new static($siteData->except('page'));
        $page_data->put('page', (new PageVariable($siteData->page))->put('_meta', new IterableObject($meta)));

        return $page_data;
    }

    public function setPageVariableToCollectionItem($collectionName, $itemName)
    {
        $this->put('page', $this->get($collectionName)->get($itemName));
    }

    public function setExtending($templateToExtend)
    {
        $this->page->_meta->put('extending', $templateToExtend);
    }

    public function setPagePath($path)
    {
        $this->page->_meta->put('path', $path);
        $this->updatePageUrl();
    }

    public function updatePageUrl()
    {
        $this->page->_meta->put('url', rightTrimPath($this->page->getBaseUrl()) . '/' . trimPath($this->page->getPath()));
    }
}
