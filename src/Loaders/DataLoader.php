<?php

namespace TightenCo\Jigsaw\Loaders;

use Illuminate\Support\Collection;
use TightenCo\Jigsaw\SiteData;

class DataLoader
{
    private $collectionDataLoader;

    public function __construct(CollectionDataLoader $collectionDataLoader)
    {
        $this->collectionDataLoader = $collectionDataLoader;
    }

    public function loadSiteData(Collection $config)
    {
        return SiteData::build($config);
    }

    public function loadCollectionData($siteData, $source)
    {
        return $this->collectionDataLoader->load($siteData, $source);
    }
}
