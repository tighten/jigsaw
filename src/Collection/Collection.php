<?php namespace TightenCo\Jigsaw\Collection;

use Closure;
use Illuminate\Support\Collection as BaseCollection;

class Collection extends BaseCollection
{
    public $settings;
    public $name;

    public static function withSettings($settings, $name)
    {
        $collection = new static;
        $collection->settings = $settings;
        $collection->name = $name;

        return $collection;
    }

    public function loadItems($items)
    {
        $sortedItems = $this->defaultSort($items)->keyBy(function ($item) {
            return $item->getFilename();
        });

        return $this->updateItems($this->addAdjacentItems($sortedItems));
    }

    public function updateItems($items)
    {
        $this->items = $this->getArrayableItems($items);

        return $this;
    }

    private function addAdjacentItems($items)
    {
        $count = $items->count();
        $adjacentItems = $items->map(function ($item) {
            return $item->getFilename();
        });
        $previousItems = $adjacentItems->prepend(null)->take($count);
        $nextItems = $adjacentItems->push(null)->take(-$count);

        return $items->each(function ($item) use ($previousItems, $nextItems) {
            $item->_meta->put('previousItem', $previousItems->shift())->put('nextItem', $nextItems->shift());
        });
    }

    private function defaultSort($items)
    {
        $sortSettings = collect(array_get($this->settings, 'sort'))->map(function ($setting) {
            return [
                'key' => ltrim($setting, '-+'),
                'direction' => $setting[0] === '-' ? -1 : 1,
            ];
        });

        if (! $sortSettings->count()) {
            return $items;
        }

        return $items->sort(function ($item_1, $item_2) use ($sortSettings) {
            return $this->compareItems($item_1, $item_2, $sortSettings);
        });
    }

    private function compareItems($item_1, $item_2, $sortSettings)
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
    }

    private function getValueForSorting($item, $key)
    {
        return strtolower($item->$key instanceof Closure ? $item->$key($item) : $item->$key);
    }
}
