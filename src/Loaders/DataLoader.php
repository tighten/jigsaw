<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Loaders;

use TightenCo\Jigsaw\SiteData;

class DataLoader
{
    /** @var CollectionDataLoader */
    private $collectionDataLoader;

    public function __construct($collectionDataLoader)
    {
        $this->collectionDataLoader = $collectionDataLoader;
    }

    public function loadSiteData($config): SiteData
    {
        return SiteData::build($config);
    }

    public function loadCollectionData($siteData, $source): array
    {
        return $this->collectionDataLoader->load($siteData, $source);
    }
}
