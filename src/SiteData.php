<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use JsonSerializable;
use Traversable;

/**
 * @property Collection $page
 * @property Collection $collections
 */
class SiteData extends IterableObject
{
    public static function build(Collection $config): SiteData
    {
        $siteData = new static();
        $siteData->putIterable('collections', $config->get('collections'));
        $siteData->putIterable('page', $config);

        return $siteData;
    }

    /**
     * @param array|Collection|Arrayable|Jsonable|JsonSerializable|Traversable $collectionData
     */
    public function addCollectionData($collectionData): Collection
    {
        collect($collectionData)->each(function ($collection, string $collectionName): Collection {
            return $this->put($collectionName, new PageVariable($collection));
        });

        return $this->forget('collections');
    }
}
