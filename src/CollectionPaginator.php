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
        $numPages = $chunked->count();
        return $chunked->map(function ($items, $i) use ($file, $numPages) {
            $pageNum = $i + 1;
            return [
                'number' => $pageNum,
                'items' => $items,
                'next' => $pageNum < $numPages ? $this->getPageLink($file, $pageNum + 1) : null,
                'prev' => $pageNum > 1 ? $this->getPageLink($file, $pageNum - 1) : null,
            ];
        });
    }

    private function getPageLink($file, $pageNum)
    {
        return $this->outputPathResolver->link(
            $file->getRelativePath(),
            $file->getBasename('.blade.php'),
            'html',
            $pageNum
        );
    }
}
