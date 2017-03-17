<?php namespace TightenCo\Jigsaw;

use TightenCo\Jigsaw\IterableObject;
use TightenCo\Jigsaw\PageVariable;

class SiteData extends IterableObject
{
    public static function build($settings)
    {
        $siteData = new static();
        $siteData->putIterable('collections', $settings->get('collections'));
        $siteData->putIterable('page', $settings);

        return $siteData;
    }

    public function addCollectionData($collectionData)
    {
        collect($collectionData)->each(function ($collection, $collectionName) {
            return $this->put($collectionName, new PageVariable($collection));
        });

        $this->forget('collections');
    }
}
