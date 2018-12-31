<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw;

/**
 * @property PageVariable page
 */
class PageData extends IterableObject
{
    public static function withPageMetaData($siteData, $meta): PageData
    {
        $page_data = new static($siteData->except('page'));
        $page_data->put('page', (new PageVariable($siteData->page))->put('_meta', new IterableObject($meta)));

        return $page_data;
    }

    public function setPageVariableToCollectionItem($collectionName, $itemName): void
    {
        $this->put('page', $this->get($collectionName)->get($itemName));
    }

    public function setExtending($templateToExtend): void
    {
        $this->page->_meta->put('extending', $templateToExtend);
    }

    public function setPagePath($path): void
    {
        $this->page->_meta->put('path', $path);
        $this->updatePageUrl();
    }

    public function updatePageUrl(): void
    {
        $this->page->_meta->put('url', rightTrimPath($this->page->getBaseUrl()) . '/' . trimPath($this->page->getPath()));
    }
}
