<?php namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\OutputFile;
use TightenCo\Jigsaw\ParsedInputFile;
use TightenCo\Jigsaw\ViewData;

class CollectionItemHandler
{
    private $collectionSettings;
    private $outputPathResolver;
    private $handlers;

    public function __construct($collectionSettings, $outputPathResolver, $handlers)
    {
        $this->collectionSettings = collect($collectionSettings);
        $this->outputPathResolver = $outputPathResolver;
        $this->handlers = collect($handlers);
    }

    public function shouldHandle($file)
    {
        return $this->isInCollectionDirectory($file);
    }

    private function isInCollectionDirectory($file)
    {
        $base = $file->topLevelDirectory();
        return starts_with($base, '_') && $this->hasCollectionNamed($this->getCollectionName($file));
    }

    private function hasCollectionNamed($candidate)
    {
        return $this->collectionSettings->has($candidate);
    }

    private function getCollectionName($file)
    {
        return substr($file->topLevelDirectory(), 1);
    }

    public function handle($file, $data)
    {
        $handler = $this->handlers->first(function ($_, $handler) use ($file) {
            return $handler->shouldHandle($file);
        });

        $viewData = ViewData::withCollectionItem($data, $this->getCollectionName($file), $file->getFilenameWithoutExtension());
        $handledFiles = $handler->handle(new ParsedInputFile($file), $viewData);

        return collect($handledFiles)->map(function ($file) {
            return new OutputFile(
                dirname($file->data()->link),
                basename($file->data()->link),
                $file->extension(),
                $file->contents(),
                $file->data());
        })->all();
    }
}
