<?php namespace TightenCo\Jigsaw\Collection;

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
        return collect(array_get($this->settings, 'sort'))
            ->reverse()
            ->reduce(function ($carry, $sortSetting) {
                return $this->sortItems($carry, $sortSetting);
            }, $items);
    }

    private function sortItems($items, $sortSetting)
    {
        $sortKey = ltrim($sortSetting, '-+');
        $sortType = $sortSetting[0] === '-' ? 'sortByDesc' : 'sortBy';
        $sortKeyFunction = $this->checkIfSortKeyIsFunction($sortKey);

        return $items->{$sortType}(function ($item, $_) use ($sortKey, $sortKeyFunction) {
            return $sortKeyFunction ?
                call_user_func_array([$item, $sortKeyFunction[0]], $sortKeyFunction[1]) :
                $item->$sortKey;
        });
    }

    private function checkIfSortKeyIsFunction($sortKey)
    {
        $sortKeyFunction = explode('(', str_replace(' ', '', $sortKey), 2);

        if (isset($sortKeyFunction[1])) {
            $parameters = explode(',', trim($sortKeyFunction[1], ')'));

            return [$sortKeyFunction[0], $parameters];
        }
    }
}
