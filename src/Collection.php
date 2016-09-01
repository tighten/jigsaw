<?php namespace TightenCo\Jigsaw;

use Illuminate\Support\Collection as BaseCollection;

class Collection extends BaseCollection
{
    private $settings;
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
        $sortedItems = $this->defaultSort($items)->keyBy(function($item) {
            return $item->filename;
        });
        $this->items = $this->getArrayableItems($this->addAdjacentItems($sortedItems));

        return $this;
    }

    public function addAdjacentItems($items)
    {
        $count = $items->count();
        $adjacentItems = $items->map(function($item) {
            return $item->filename;
        });
        $previousItems = $adjacentItems->prepend(null)->take($count);
        $nextItems = $adjacentItems->push(null)->take(-$count);

        return $items->map(function($item) use ($previousItems, $nextItems) {
            return $item->put('_previousItem', $previousItems->shift())->put('_nextItem', $nextItems->shift());
        });
    }

    public function getDefaultVariables()
    {
        return array_get($this->settings, 'variables', []);
    }

    public function getPermalink()
    {
        return array_get($this->settings, 'path');
    }

    public function getHelpers()
    {
        return array_get($this->settings, 'helpers', []);
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
            $parameterss = explode(',', trim($sortKeyFunction[1], ')'));

            return [$sortKeyFunction[0], $parameterss];
        }
    }
}
