<?php namespace TightenCo\Jigsaw;

class DataLoader
{
    private $collectionDataLoader;

    public function __construct($collectionDataLoader)
    {
        $this->collectionDataLoader = $collectionDataLoader;
    }

    public function load($source, $config)
    {
        $siteData = SiteData::build($config);
        $siteData->addCollectionData($this->collectionDataLoader->load($source, $siteData));

        return $siteData;
    }
}
