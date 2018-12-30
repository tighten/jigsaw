<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Loaders;

use TightenCo\Jigsaw\SiteData;

class DataLoader
{
    private $collectionDataLoader;

    public function __construct($collectionDataLoader)
    {
        $this->collectionDataLoader = $collectionDataLoader;
    }

    public function loadSiteData($config)
    {
        return SiteData::build($config);
    }

    public function loadCollectionData($siteData, $source)
    {
        return $this->collectionDataLoader->load($siteData, $source);
    }
}
