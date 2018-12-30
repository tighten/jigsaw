<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw;

use Illuminate\Support\Collection;

class SiteData extends IterableObject
{
    public static function build($config): SiteData
    {
        $siteData = new static();
        $siteData->putIterable('collections', $config->get('collections'));
        $siteData->putIterable('page', $config);

        return $siteData;
    }

    public function addCollectionData($collectionData): Collection
    {
        collect($collectionData)->each(function ($collection, $collectionName): Collection {
            return $this->put($collectionName, new PageVariable($collection));
        });

        return $this->forget('collections');
    }
}
