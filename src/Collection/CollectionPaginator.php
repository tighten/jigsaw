<?php

namespace TightenCo\Jigsaw\Collection;

use TightenCo\Jigsaw\IterableObject;

class CollectionPaginator
{
    private $outputPathResolver;

    private $prefix;

    public function __construct($outputPathResolver)
    {
        $this->outputPathResolver = $outputPathResolver;
    }

    public function paginate(string $relativePath, string $filename, $items, $perPage, string $prefix = '')
    {
        $chunked = collect($items)->chunk($perPage);
        $totalPages = $chunked->count();
        $this->prefix = $prefix;
        $numberedPageLinks = $chunked->map(function ($_, $i) use ($relativePath, $filename) {
            $page = $i + 1;

            return ['number' => $page, 'path' => $this->getPageLink($relativePath, $filename, $page)];
        })->pluck('path', 'number');

        return $chunked->map(function ($items, $i) use ($relativePath, $filename, $totalPages, $numberedPageLinks) {
            $currentPage = $i + 1;

            return new IterableObject([
                'items' => $items,
                'previous' => $currentPage > 1 ? $this->getPageLink($relativePath, $filename, $currentPage - 1) : null,
                'current' => $this->getPageLink($relativePath, $filename, $currentPage),
                'next' => $currentPage < $totalPages ? $this->getPageLink($relativePath, $filename, $currentPage + 1) : null,
                'first' => $this->getPageLink($relativePath, $filename, 1),
                'last' => $this->getPageLink($relativePath, $filename, $totalPages),
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'pages' => $numberedPageLinks,
            ]);
        });
    }

    private function getPageLink(string $relativePath, string $filename, int $pageNumber): string
    {
        $link = $this->outputPathResolver->link(
            $relativePath,
            $filename,
            'html',
            $pageNumber,
            $this->prefix,
        );

        return $link !== '/' ? rightTrimPath($link) : $link;
    }
}
