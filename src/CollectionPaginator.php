<?php namespace TightenCo\Jigsaw;

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
            return [
                'items' => $items,
                'next' => $currentPage < $totalPages ? $this->getPageLink($file, $currentPage + 1) : null,
                'previous' => $currentPage > 1 ? $this->getPageLink($file, $currentPage - 1) : null,
                'first' => $this->getPageLink($file, 1),
                'last' => $this->getPageLink($file, $totalPages),
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'pages' => $numberedPageLinks,
            ];
        });
    }

    private function getPageLink($file, $pageNumber)
    {
        return rtrim($this->outputPathResolver->link(
            $file->getRelativePath(),
            $file->getFilenameWithoutExtension(),
            'html',
            $pageNumber
        ), '/');
    }
}
