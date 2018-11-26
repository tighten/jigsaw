<?php

namespace TightenCo\Jigsaw\Collection;

use TightenCo\Jigsaw\IterableObject;

class CollectionPaginator
{
    private $outputPathResolver;

    public function __construct($outputPathResolver)
    {
        $this->outputPathResolver = $outputPathResolver;
    }

    public function paginate($file, $items, $perPage)
    {
        $chunked = collect($items)->chunk($perPage);
        $totalPages = $chunked->count();
        $numberedPageLinks = $chunked->map(function ($_, $i) use ($file) {
            $page = $i + 1;

            return ['number' => $page, 'path' => $this->getPageLink($file, $page)];
        })->pluck('path', 'number');

        return $chunked->map(function ($items, $i) use ($file, $totalPages, $numberedPageLinks) {
            $currentPage = $i + 1;

            return new IterableObject([
                'items' => $items,
                'previous' => $currentPage > 1 ? $this->getPageLink($file, $currentPage - 1) : null,
                'current' => $this->getPageLink($file, $currentPage),
                'next' => $currentPage < $totalPages ? $this->getPageLink($file, $currentPage + 1) : null,
                'first' => $this->getPageLink($file, 1),
                'last' => $this->getPageLink($file, $totalPages),
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'pages' => $numberedPageLinks,
            ]);
        });
    }

    private function getPageLink($file, $pageNumber)
    {
        $link = $this->outputPathResolver->link(
            $file->getRelativePath(),
            $file->getFilenameWithoutExtension(),
            'html',
            $pageNumber
        );

        return $link !== '/' ? rightTrimPath($link) : $link;
    }
}
