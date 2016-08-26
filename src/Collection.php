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
        parent::__construct(
            $this->defaultSort($items->keyBy(function($item) {
                return $item->filename;
            }))
        );

        return $this;
    }

    public function getDefaultVariables()
    {
        return array_get($this->settings, 'variables', []);
    }

    public function getPermalink()
    {
        return array_get($this->settings, 'permalink') ?: function($data) {
            return slugify($data['filename']);
        };
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
        $sortFunction = $sortSetting[0] === '-' ? 'sortByDesc' : 'sortBy';

        return $items->{$sortFunction}(function ($item, $_) use ($sortKey) {
            return $item->{$sortKey};
        });
    }
}
