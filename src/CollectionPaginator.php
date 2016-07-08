<?php namespace TightenCo\Jigsaw;

class CollectionPaginator
{
    private $items;
    private $perPage;

    public function __construct($items, $perPage)
    {
        $this->items = collect($items);
        $this->perPage = $perPage;
    }

    public function pages()
    {
        return $this->items->chunk($this->perPage)->map(function ($items, $i) {
            return [
                'number' => $i + 1,
                'items' => $items,
            ];
        });
    }
}
