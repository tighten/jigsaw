<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Collection;

use Illuminate\Support\Collection;
use TightenCo\Jigsaw\IterableObject;
use TightenCo\Jigsaw\PathResolvers\BasicOutputPathResolver;

class CollectionPaginator
{
    /** @var BasicOutputPathResolver */// TODO use interface instead of class
    private $outputPathResolver;

    public function __construct(BasicOutputPathResolver $outputPathResolver)
    {
        $this->outputPathResolver = $outputPathResolver;
    }

    public function paginate($file, $items, $perPage): Collection
    {
        $chunked = collect($items)->chunk($perPage);
        $totalPages = $chunked->count();
        $numberedPageLinks = $chunked->map(function ($_, $i) use ($file): array {
            $page = $i + 1;

            return ['number' => $page, 'path' => $this->getPageLink($file, $page)];
        })->pluck('path', 'number');

        return $chunked->map(function ($items, $i) use ($file, $totalPages, $numberedPageLinks): IterableObject {
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

    private function getPageLink($file, $pageNumber): string
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
