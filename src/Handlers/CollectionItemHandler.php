<?php namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\OutputFile;

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

    private function getCollectionData($file, $collectionName)
    {
        return collect($file->getCollectionData($collectionName))->first(function($_, $item) use ($file) {
            return $item->getFilename() === $file->name();
        });
    }

    public function handle($file, $data)
    {
        $handler = $this->handlers->first(function ($_, $handler) use ($file) {
            return $handler->shouldHandle($file);
        });
        $collectionName = $this->getCollectionName($file);
        $defaultVariables = array_get($this->collectionSettings[$collectionName], 'variables', []);
        $handledFiles = $handler->handle($file, array_merge($defaultVariables, $data));

        return collect($handledFiles)->map(function ($file) use ($collectionName) {
            $link = $this->getCollectionData($file, $collectionName)->getLink();

            return new OutputFile(dirname($link), basename($link), $file->extension(), $file->contents(), $file->data());
        })->all();
    }
}
