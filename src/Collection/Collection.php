<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Collection;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection as BaseCollection;
use JsonSerializable;
use TightenCo\Jigsaw\File\InputFile;
use TightenCo\Jigsaw\IterableObject;
use Traversable;

class Collection extends BaseCollection
{
    /** @var IterableObject */
    public $settings;

    /** @var string */
    public $name;

    public static function withSettings(IterableObject $settings, string $name): Collection
    {
        $collection = new static();
        $collection->settings = $settings;
        $collection->name = $name;

        return $collection;
    }

    public function loadItems(BaseCollection $items): Collection
    {
        $sortedItems = $this->defaultSort($items)->keyBy(function (InputFile $item): string {
            return $item->getFilename();
        });

        return $this->updateItems($this->addAdjacentItems($sortedItems));
    }

    /**
     * @param array|Collection|Arrayable|Jsonable|JsonSerializable|Traversable $items
     */
    public function updateItems($items): Collection
    {
        $this->items = $this->getArrayableItems($items);

        return $this;
    }

    private function addAdjacentItems(BaseCollection $items): BaseCollection
    {
        $count = $items->count();
        $adjacentItems = $items->map(function (InputFile $item): string {
            return $item->getFilename();
        });
        $previousItems = $adjacentItems->prepend(null)->take($count);
        $nextItems = $adjacentItems->push(null)->take(-$count);

        return $items->each(function ($item) use ($previousItems, $nextItems): void {
            $item->_meta->put('previousItem', $previousItems->shift())->put('nextItem', $nextItems->shift());
        });
    }

    private function defaultSort(BaseCollection $items): BaseCollection
    {
        $sortSettings = collect(array_get($this->settings, 'sort'))->map(function (string $setting): array {
            return [
                'key' => ltrim($setting, '-+'),
                'direction' => $setting[0] === '-' ? -1 : 1,
            ];
        });

        if (! $sortSettings->count()) {
            return $items;
        }

        return $items->sort(function ($item_1, array $item_2) use ($sortSettings): int {
            return $this->compareItems($item_1, $item_2, $sortSettings);
        });
    }

    private function compareItems($item_1, $item_2, $sortSettings): ?int
    {
        foreach ($sortSettings as $setting) {
            $value_1 = $this->getValueForSorting($item_1, array_get($setting, 'key'));
            $value_2 = $this->getValueForSorting($item_2, array_get($setting, 'key'));

            if ($value_1 > $value_2) {
                return $setting['direction'];
            } elseif ($value_1 < $value_2) {
                return -$setting['direction'];
            }
        }

        return null;
    }

    /**
     * @param object $item
     */
    private function getValueForSorting($item, string $key): string
    {
        return strtolower($item->$key instanceof Closure ? $item->$key($item) : $item->$key);
    }
}
